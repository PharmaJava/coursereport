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

    // require_once '../../../../config.php';

    // global $DB, $USER;
    
    // header('Content-Type: application/json');
    // // Verificar login y permisos necesarios.
    // require_login();
    // if (!is_siteadmin() && !has_capability('moodle/course:viewparticipants', context_system::instance())) {
    //     echo json_encode(['error' => 'No tienes permisos suficientes para realizar esta acción.']);
    //     exit;
    // }
    
    // $courseid = required_param('courseid', PARAM_INT);
    
    // $sql = "SELECT u.id, u.firstname, u.lastname
    //         FROM {user} u
    //         JOIN {user_enrolments} ue ON ue.userid = u.id
    //         JOIN {enrol} e ON e.id = ue.enrolid
    //         WHERE e.courseid = :courseid
    //         AND ue.status = 0
    //         AND u.deleted = 0
    //         AND u.suspended = 0
    //         AND EXISTS (
    //             SELECT 1
    //             FROM {role_assignments} ra
    //             JOIN {context} ctx ON ctx.id = ra.contextid
    //             WHERE ctx.instanceid = e.courseid
    //             AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'student')
    //             AND ra.userid = u.id
    //         )";
    
    // try {
    //     $students = $DB->get_records_sql($sql, ['courseid' => $courseid]);
    //     $results = array_map(function($student) {
    //         return [
    //             'id' => $student->id,
    //             'firstname' => $student->firstname,
    //             'lastname' => $student->lastname
    //         ];
    //     }, array_values($students));
    //     echo json_encode($results);
    // } catch (Exception $e) {
    //     echo json_encode(['error' => 'Error al obtener los estudiantes del curso.']);
    // }


require_once('../../../../config.php');  
global $DB, $OUTPUT;

require_login();  
$context = context_system::instance();
require_capability('moodle/site:viewreports', $context); 

$courseid = required_param('courseid', PARAM_INT);  // Obtener el ID del curso de los parámetros de la solicitud.

header('Content-Type: application/html');  

try {
    // Consulta SQL para obtener los alumnos matriculados que no están borrados ni suspendidos.
    $sql = "SELECT u.id, u.firstname, u.lastname
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 50
            WHERE e.courseid = :courseid
            AND ue.status = 0
            AND u.deleted = 0
            AND u.suspended = 0
            AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'student')
            AND ctx.instanceid = e.courseid";
    
    $students = $DB->get_records_sql($sql, ['courseid' => $courseid]);
    $data = ['students' => array_values($students)];  // Prepara los datos para la plantilla Mustache.

    // Renderiza la plantilla Mustache y devuelve el HTML.
    echo $OUTPUT->render_from_template('report_coursereport/listaestudiantes', $data);
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al obtener los estudiantes del curso: ' . $e->getMessage();
}
