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
 * Renderer for report_coursereport.
 *
 * @package     report_coursereport
 * @copyright   2024 Antonio <your@email>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursereport\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {

    public function render_search_results($courses, $totalcount, $page, $perpage, $baseurl) {
        
        $downloadButton = \html_writer::div(
            \html_writer::tag('button', get_string('downloadcsv', 'report_coursereport'), [
                'type' => 'button', 
                'id' => 'download-csv', 
                'class' => 'btn btn-primary'
            ]),
            'download-button-container',
            ['style' => 'margin-bottom: 10px;']
        );
        $downloadButtonExcel = \html_writer::div(
            \html_writer::tag('button', get_string('downloadexcel', 'report_coursereport'), [
                'type' => 'button', 
                'id' => 'download-excel',
                'class' => 'btn btn-primary'
            ]),
            'download-button-container',
            ['style' => 'margin-bottom: 10px;']
        );
        // Barra de paginación
        $pagingbar = new \paging_bar($totalcount, $page, $perpage, $baseurl);
        $pagingbarhtml = $this->render($pagingbar);
    
        // Información sobre el número de cursos visualizados
        $currentfrom = $page * $perpage + 1;
        $currentto = min(($page + 1) * $perpage, $totalcount);
        $info = "Mostrando {$currentfrom}–{$currentto} de {$totalcount} cursos";
        $infohtml = \html_writer::div($info, 'pagination-info', ['style' => 'margin-bottom: 10px; font-weight: bold;']);
    
        // Construcción del HTML para los cursos
        $courseshmtl = '';
        if (!empty($courses)) {
            $courseshmtl = \html_writer::start_tag('table', ['class' => 'generaltable table table-bordered', 'style' => 'width: 100%; margin-top: 10px; border-collapse: collapse;']);
            $header = \html_writer::start_tag('thead');
            $header .= \html_writer::start_tag('tr');
            $header .= \html_writer::tag('th', 'Nombre Corto', ['scope' => 'col', 'style' => 'border-bottom: 2px solid #ccc; padding: 8px;']);
            $header .= \html_writer::tag('th', 'Nombre Largo', ['scope' => 'col', 'style' => 'border-bottom: 2px solid #ccc; padding: 8px;']);
            $header .= \html_writer::end_tag('tr');
            $header .= \html_writer::end_tag('thead');
            $courseshmtl .= $header;

            $body = \html_writer::start_tag('tbody');
            foreach ($courses as $course) {
                $body .= \html_writer::start_tag('tr');
                $body .= \html_writer::tag('td', htmlspecialchars($course->shortname, ENT_QUOTES, 'UTF-8'), ['style' => 'padding: 8px; border-bottom: 1px solid #eee;']);
                $body .= \html_writer::tag('td', htmlspecialchars($course->fullname, ENT_QUOTES, 'UTF-8'), ['style' => 'padding: 8px; border-bottom: 1px solid #eee;']);
                $body .= \html_writer::end_tag('tr');
            }
            $body .= \html_writer::end_tag('tbody');
            $courseshmtl .= $body;
            $courseshmtl .= \html_writer::end_tag('table');
        } else {
            $courseshmtl .= $this->output->notification("No hay cursos que coincidan con los criterios de búsqueda.", 'notifymessage');
        }
    
        // Concatenación del HTML total
        return $infohtml . $pagingbarhtml . $courseshmtl . $pagingbarhtml . $downloadButton. $downloadButtonExcel;
    }
}
