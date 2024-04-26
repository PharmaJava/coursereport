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
require_once('../../../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

global $DB, $PAGE;

 // Verificar que el usuario esté autenticado y tenga los permisos necesarios.
require_login();
$courseid = required_param('courseid', PARAM_INT);  // Obtener el ID del curso desde los parámetros de la URL.
$context = context_course::instance($courseid);  // Obtener el contexto del curso.
require_capability('moodle/course:viewparticipants', $context);  // Verificar permisos.

 // Obtener datos del curso asegurando que el curso existe
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$completion = new completion_info($course);  // Instancia para manejar la información de finalización del curso.

// Preparar la consulta SQL para obtener los estudiantes y sus calificaciones finales.
$sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.username,
        gg.finalgrade
    FROM {user} u
    JOIN {user_enrolments} ue ON ue.userid = u.id
    JOIN {enrol} e ON e.id = ue.enrolid
    JOIN {role_assignments} ra ON ra.userid = u.id
    JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50 AND ctx.instanceid = e.courseid
    JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
    LEFT JOIN {grade_grades} gg ON gg.userid = u.id
    LEFT JOIN {grade_items} gi ON gi.id = gg.itemid AND gi.courseid = e.courseid AND gi.itemtype = 'course'
    WHERE e.courseid = :courseid AND ue.status = 0 AND u.deleted = 0 AND u.suspended = 0
    GROUP BY u.id";

$params = ['courseid' => $courseid];
$students = $DB->get_records_sql($sql, $params);  // Ejecutar la consulta y obtener los registros.

$studentData = [];
foreach ($students as $student) {
     // Calcular el porcentaje de completitud de cada estudiante
    $completions = $completion->get_completions($student->id);
    $completed = count(array_filter($completions, function ($comp) {
         return $comp->is_complete();  // Filtrar sólo las actividades completadas.
    }));
    $total = count($completions);
     $completionpercent = $total > 0 ? round(($completed / $total) * 100) : 0;  // Calcular el porcentaje.

     // Preparar los datos de los estudiantes para la plantilla Mustache.
    $studentData[] = [
        'firstname' => $student->firstname,
        'lastname' => $student->lastname,
        'email' => $student->email,
        'username' => $student->username,
         'completion' => "{$completionpercent}%"  // Añadir el porcentaje de completitud.
    ];
}

 // Renderizar la plantilla Mustache con los datos preparados.
$renderer = $PAGE->get_renderer('report_coursereport');
echo $renderer->render_from_template('report_coursereport/listaestudiantes', ['students' => $studentData]);

