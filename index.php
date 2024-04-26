<?php
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
 * Plugin version and other meta-data are defined here.
 *
 * @package     report_coursereport
 * @copyright   2024 Antonio <your@email>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/coursereport/classes/form/formulario_form.php');
require_once($CFG->dirroot . '/report/coursereport/lib.php');

// Obtener el título del plugin.
$title = get_string('pluginname', 'report_coursereport');

// Configurar contexto, URL, título y encabezado de la página.
$context = context_system::instance();
require_capability('report/coursereport:view', $context);

$PAGE->set_url(new moodle_url('/report/coursereport/index.php'));
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Requerir inicio de sesión y cargar script JavaScript necesario.
$PAGE->requires->js(new moodle_url('/report/coursereport/js/logica.js'));
require_login();

// Crear formulario de búsqueda y obtener renderizador.
$searchform = new \report_coursereport\form\formulario_form();
$renderer = $PAGE->get_renderer('report_coursereport');

// Imprimir encabezado y título de la página.
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Parámetros opcionales obtenidos de la URL o valores predeterminados.
$shortname = optional_param('shortname', '', PARAM_NOTAGS);
$fullname = optional_param('fullname', '', PARAM_NOTAGS);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$count = optional_param('count', 0, PARAM_INT);

// Si el conteo es cero, asumir que es la primera carga o necesita recálculo.
if ($count == 0 && (!empty($shortname) || !empty($fullname))) {
    $count = count_courses_by_search($shortname, $fullname);
}

// Asegurar que el formulario muestre los datos actuales.
// $searchform->set_data(['shortname' => $shortname, 'fullname' => $fullname]);

// Si hay términos de búsqueda, buscar cursos; de lo contrario, el array de cursos está vacío.
if (!empty($shortname) || !empty($fullname)) {
    $courses = fetch_courses_by_search($shortname, $fullname, $page * $perpage, $perpage);

        $SESSION->coursereport_filter = $courses;
} else {
        $courses = [];
        $count = 0;
}

// Mostrar formulario de búsqueda.
$searchform->display();

// Configuración de URL base para la paginación que retiene los criterios de búsqueda y el conteo.
$baseurl = new moodle_url('/report/coursereport/index.php', [
    'shortname' => $shortname,
    'fullname' => $fullname,
    'count' => $count]);

// Mostrar resultados de búsqueda y paginación.

if ($count > 0) {
    echo $renderer->render_search_results($courses, $count, $page, $perpage, $baseurl);
}

echo $OUTPUT->footer();