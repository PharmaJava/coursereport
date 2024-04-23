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


require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/coursereport/classes/form/formulario_form.php');

admin_externalpage_setup('reportcoursereport');

$PAGE->set_title(get_string('pluginname', 'report_coursereport'));
$PAGE->set_heading(get_string('pluginname', 'report_coursereport'));
$PAGE->set_url(new moodle_url('/report/coursereport/formulario.php'));

 // Crear una instancia del formulario.
$form = new \report_coursereport\classes\form\myform();

 // Verificar si el formulario fue cancelado.
if ($form->is_cancelled()) {
     // Redirigir si es necesario.
    redirect(new moodle_url('/admin/report/coursereport/index.php'));
}

 // Verificar si se recibieron datos del formulario.
if ($data = $form->get_data()) {

     // Redirigir a otra página después de procesar los datos.
    redirect(new moodle_url('/admin/report/coursereport/index.php'));
}

 // Mostrar la página del formulario.
echo $OUTPUT->header();
$form->display();

echo $OUTPUT->footer();
