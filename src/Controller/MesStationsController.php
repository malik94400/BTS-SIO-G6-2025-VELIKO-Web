<?php

namespace App\Controller;

use App\Entity\Station;
use App\Entity\StationUser;
use App\Entity\User;
use App\Repository\StationRepository;
use App\Repository\StationUserRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MesStationsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private StationRepository $stationRepository;
    private StationUserRepository $stationUserRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        StationRepository $stationRepository,
        StationUserRepository $stationUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->stationRepository = $stationRepository;
        $this->stationUserRepository = $stationUserRepository;
    }


    #[Route('/mes/stations', name: 'app_mes_stations')] // Définition de la route '/mes/stations' avec le nom 'app_mes_stations'
    public function index(): Response // La méthode index retourne une réponse HTTP
    {
        // Récupère toutes les stations disponibles depuis le repository
        $stations = $this->stationRepository->findAll();

        // Récupère l'utilisateur actuellement connecté
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId(); // Extrait l'ID de l'utilisateur connecté

        // Obtient le repository de StationUser pour gérer les stations favorites de l'utilisateur
        /** @var StationUserRepository $stationUserRepository */
        $stationUserRepository = $this->entityManager->getRepository(StationUser::class);

        // Récupère les enregistrements des stations favorites de l'utilisateur
        $stationUserRecords = $stationUserRepository->findStationsByUserId($userId);

        // Prépare les tableaux pour stocker les ID et les noms des stations favorites
        $favoriteStationIds = [];
        $stationNames = [];

        // Parcourt chaque enregistrement des stations favorites
        foreach ($stationUserRecords as $record) {
            $idStation = $record["id_station"]; // Extrait l'ID de la station favorite
            $stationData = $stationUserRepository->findStationNameById($idStation); // Récupère les données de la station

            // Si la station existe, extrait son nom et l'ajoute aux tableaux
            if (!empty($stationData)) {
                $stationName = $stationData[0]["name"]; // Extrait le nom de la station
                $stationNames[] = [
                    'name' => $stationName,
                    'id' => $idStation
                ]; // Ajoute un tableau associatif avec le nom et l'ID de la station
                $favoriteStationIds[] = $idStation; // Ajoute l'ID de la station aux favoris
            }
        }

        // Rend la vue avec les données des stations et des stations favorites
        return $this->render('mes_stations/index.html.twig', [
            'controller_name' => 'MesStationsController',
            'station_names' => $stationNames, // Noms et ID des stations favorites
            'stations' => $stations, // Toutes les stations
            'favoriteStationIds' => $favoriteStationIds // IDs des stations favorites
        ]);
    }

    #[Route('/station/delete/{id}', name: 'app_station_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        /** @var StationUserRepository $stationUserRepository */
        $stationUserRepository = $this->entityManager->getRepository(StationUser::class);

        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();

        $idStation = $request->get("id");

        if ($idStation) {
//            $this->entityManager->remove($idStation); impossible, car on n'a que des id dans l'entité stationUser pas d'object user et station
            $stationUserRepository->deleteStationByStationId($idStation);
            $this->entityManager->flush();

            $this->addFlash('Succès', 'Station supprimée.');
        } else {
            $this->addFlash('Erreur', 'Station non trouvée.');
        }

        return $this->redirectToRoute('app_mes_stations');

    }

    #[Route('/station/add_favorite/{id}', name: 'app_add_favorite', methods: ['POST'])]
    public function addFavorite(int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();

        // Vérifier si la station est déjà dans les favoris de l'utilisateur
        $stationUserRepository = $this->entityManager->getRepository(StationUser::class);
        $existingFavorite = $stationUserRepository->findOneBy([
            'id_user' => $userId,
            'id_station' => $id,
        ]);

        if ($existingFavorite) {
            $this->addFlash('info', 'Station déjà dans les favoris.');
        } else {
            $stationUser = new StationUser();
            $stationUser->setIdUser($userId);
            $stationUser->setIdStation($id);

            $this->entityManager->persist($stationUser);
            $this->entityManager->flush();

        }


        return $this->redirectToRoute('app_mes_stations');

    }

}