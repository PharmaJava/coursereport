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
/**
 * Obtiene cursos filtrados por búsqueda.
 *
 * @param string $shortname Nombre corto del curso.
 * @param string $fullname  Nombre largo del curso.
 * @param int    $offset    Desplazamiento de resultados.
 * @param int    $perpage   Número de resultados por página.
 * @return array            Array de registros de cursos.
 */
function fetch_courses_by_search($shortname, $fullname, $offset, $perpage) {
    global $DB;
    $params = [];
    $conditions = [];

    // Agregar condiciones de búsqueda basadas en los nombres corto y largo del curso.
    if (!empty($shortname)) {
        $conditions[] = $DB->sql_like('shortname', ':shortname', false);
        $params['shortname'] = '%' . $shortname . '%';
    }
    if (!empty($fullname)) {
        $conditions[] = $DB->sql_like('fullname', ':fullname', false);
        $params['fullname'] = '%' . $fullname . '%';
    }

    $sqlwhere = $conditions ? " WHERE " . implode(' AND ', $conditions) : '';

    $sql = "SELECT * FROM {course} $sqlwhere ORDER BY id ASC";
    return $DB->get_records_sql($sql, $params, $offset, $perpage);
}
/**
 * Cuenta cursos filtrados por búsqueda.
 *
 * @param string $shortname Nombre corto del curso.
 * @param string $fullname  Nombre largo del curso.
 * @return int              Número total de cursos encontrados.
 */
function count_courses_by_search($shortname, $fullname) {
    global $DB;
    $params = [];
    $conditions = [];

    // Agregar condiciones de búsqueda para contar cursos.
    if (!empty($shortname)) {
        $conditions[] = $DB->sql_like('shortname', ':shortname', false);
        $params['shortname'] = '%' . $shortname . '%';
    }
    if (!empty($fullname)) {
        $conditions[] = $DB->sql_like('fullname', ':fullname', false);
        $params['fullname'] = '%' . $fullname . '%';
    }

    $where = $conditions ? " WHERE " . implode(' AND ', $conditions) : '';

    $sql = "SELECT COUNT(id) FROM {course} $where";
    return $DB->count_records_sql($sql, $params);
}

