<?php
require_once 'functions.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gestion des Horaires de Trains</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100">
    <div class="min-h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 bg-blue-900 text-white">
            <div class="p-4 border-b border-blue-800">
                <h1 class="text-xl font-bold">Admin Dashboard</h1>
                <p class="text-sm text-blue-200">Gestion des Horaires de Trains</p>
            </div>
            <nav class="p-4 space-y-2">
                <a href="#" class="block px-4 py-2 rounded bg-blue-800">
                    <i class="fas fa-train mr-2"></i> Gestion des Trains
                </a>
                <a href="#bookings" class="block px-4 py-2 rounded hover:bg-blue-800">
                    <i class="fas fa-ticket-alt mr-2"></i> Réservations
                </a>
                <a href="#users" class="block px-4 py-2 rounded hover:bg-blue-800">
                    <i class="fas fa-users mr-2"></i> Utilisateurs
                </a>
                <a href="index.html" class="block px-4 py-2 rounded hover:bg-blue-800">
                    <i class="fas fa-home mr-2"></i> Retour au site
                </a>
                <a href="api/logout.php" class="block px-4 py-2 rounded hover:bg-blue-800">
                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Gestion des Trains</h2>
                <button id="addTrainBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-plus mr-2"></i> Ajouter un Train
                </button>
            </div>

            <!-- Trains Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Départ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrivée</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Places</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="trainsTable" class="bg-white divide-y divide-gray-200">
                        <!-- Trains will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Add/Edit Train Modal -->
            <div id="trainModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold" id="modalTitle">Ajouter un Train</h3>
                        <button id="closeModalBtn" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="trainForm">
                        <input type="hidden" id="trainId">
                        <div class="space-y-4">
                            <div>
                                <label for="trainName" class="block text-sm font-medium text-gray-700">Nom du Train</label>
                                <input type="text" id="trainName" name="trainName" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="origin" class="block text-sm font-medium text-gray-700">Gare de Départ</label>
                                <input type="text" id="origin" name="origin" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700">Gare d'Arrivée</label>
                                <input type="text" id="destination" name="destination" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="departureTime" class="block text-sm font-medium text-gray-700">Heure de Départ</label>
                                    <input type="time" id="departureTime" name="departureTime" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="arrivalTime" class="block text-sm font-medium text-gray-700">Heure d'Arrivée</label>
                                    <input type="time" id="arrivalTime" name="arrivalTime" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            <div>
                                <label for="seatsAvailable" class="block text-sm font-medium text-gray-700">Places Disponibles</label>
                                <input type="number" id="seatsAvailable" name="seatsAvailable" min="1" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" id="cancelBtn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Annuler
                            </button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadTrains();
            setupModal();
        });

        function loadTrains() {
            fetch('api/trains.php')
                .then(response => response.json())
                .then(trains => {
                    const tableBody = document.getElementById('trainsTable');
                    tableBody.innerHTML = '';
                    
                    trains.forEach(train => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap">${train.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${train.origin}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${train.destination}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${train.departure_time}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${train.arrival_time}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${train.seats_available}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="editTrain(${train.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteTrain(${train.id})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                });
        }

        function setupModal() {
            const modal = document.getElementById('trainModal');
            const addBtn = document.getElementById('addTrainBtn');
            const closeBtn = document.getElementById('closeModalBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const form = document.getElementById('trainForm');

            addBtn.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Ajouter un Train';
                document.getElementById('trainId').value = '';
                form.reset();
                modal.classList.remove('hidden');
            });

            closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
            cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const id = document.getElementById('trainId').value;
                const method = id ? 'PUT' : 'POST';
                const url = 'api/trains.php' + (id ? `?id=${id}` : '');

                const trainData = {
                    name: document.getElementById('trainName').value,
                    origin: document.getElementById('origin').value,
                    destination: document.getElementById('destination').value,
                    departure_time: document.getElementById('departureTime').value,
                    arrival_time: document.getElementById('arrivalTime').value,
                    seats_available: document.getElementById('seatsAvailable').value
                };

                if (id) trainData.id = id;

                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(trainData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.classList.add('hidden');
                        loadTrains();
                    } else {
                        alert('Erreur: ' + (data.error || 'Une erreur est survenue'));
                    }
                });
            });
        }

        function editTrain(id) {
            fetch(`api/trains.php?id=${id}`)
                .then(response => response.json())
                .then(train => {
                    document.getElementById('modalTitle').textContent = 'Modifier le Train';
                    document.getElementById('trainId').value = train.id;
                    document.getElementById('trainName').value = train.name;
                    document.getElementById('origin').value = train.origin;
                    document.getElementById('destination').value = train.destination;
                    document.getElementById('departureTime').value = train.departure_time;
                    document.getElementById('arrivalTime').value = train.arrival_time;
                    document.getElementById('seatsAvailable').value = train.seats_available;
                    
                    document.getElementById('trainModal').classList.remove('hidden');
                });
        }

        function deleteTrain(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce train ?')) {
                fetch(`api/trains.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadTrains();
                    } else {
                        alert('Erreur lors de la suppression');
                    }
                });
            }
        }
    </script>
</body>
</html>