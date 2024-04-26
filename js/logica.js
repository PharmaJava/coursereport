// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Renderer for report_coursereport.
 *
 * @package     report_coursereport
 * @copyright   2024 Antonio <your@email>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
document.addEventListener('DOMContentLoaded', function() {
    var downloadButton = document.getElementById('download-button');
    var downloadSelect = document.getElementById('downloadformat');

    downloadButton.addEventListener('click', function() {
        var selectedOption = downloadSelect.options[downloadSelect.selectedIndex];
        if (selectedOption.value) {
            window.location.href = selectedOption.value;
        }
    });
});


document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ver-alumnos-btn').forEach(button => {
        button.addEventListener('click', function() {
            var courseid = this.getAttribute('data-courseid');
            var url = '/moodle/report/coursereport/classes/ajax/estudiantes.php?courseid=' + courseid;
            var container = this.closest('tr').nextElementSibling; // Asegura que este es el contenedor correcto.

            // Cambia la visibilidad del contenedor dependiendo de su estado actual.
            if (container.style.display === 'none' || container.style.display === '') {
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('La respuesta de la red no fue satisfactoria.');
                        }
                        return response.text();
                    })
                    .then(html => {
                        container.querySelector('td').innerHTML = html; // Inserta el HTML en el contenedor.
                        container.style.display = 'table-row'; // Muestra el contenedor.
                    })
                    .catch(error => {
                        container.querySelector('td').innerHTML = 'Error al cargar los datos.';
                        container.style.display = 'table-row';
                    });
            } else {
                container.style.display = 'none'; // Oculta los detalles si ya están visibles.
            }
        });
    });
});



