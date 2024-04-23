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



/**
 * Class renderer
 *
 * Renderer for report_coursereport.
 */
class renderer extends \plugin_renderer_base {

    /**
     * Render search results.
     *
     * @param array  $courses   Array de cursos.
     * @param int    $totalcount Total numero de cursos.
     * @param int    $page      Pagina actual.
     * @param int    $perpage   Numero de cursos por pagina.
     * @param string $baseurl   Base URL.
     * @return string Rendered HTML.
     */
    public function render_search_results($courses, $totalcount, $page, $perpage, $baseurl) {
            $url = new \moodle_url('/report/coursereport/classes/descargaarchivos.php', ['action' => 'csv']);
            $downloadbuttoncsv = \html_writer::div(
                \html_writer::link($url, get_string('downloadcsv', 'report_coursereport'), [
                    'type' => 'link',
                    'id' => 'download-csv',
                    'class' => 'btn btn-primary']),
                'download-button-container',
                ['style' => 'margin-bottom: 10px;']
            );

            $url2 = new \moodle_url('/report/coursereport/classes/descargaarchivos.php', ['action' => 'excel']);
            $downloadbuttonexcel = \html_writer::div(
                \html_writer::link($url2, get_string('downloadexcel', 'report_coursereport'), [
                    'type' => 'link',
                    'id' => 'download-excel',
                    'class' => 'btn btn-primary']),
                'download-button-container',
                ['style' => 'margin-bottom: 10px;']
            );
        $urlcsv = new \moodle_url('/report/coursereport/classes/descargaarchivos.php', ['action' => 'csv']);
        $urlexcel = new \moodle_url('/report/coursereport/classes/descargaarchivos.php', ['action' => 'excel']);
        $downloadoptions = [
            (string) $urlcsv => get_string('downloadascsv', 'report_coursereport'),
            (string) $urlexcel => get_string('downloadasexcel', 'report_coursereport')];

        $downloadselect = \html_writer::select(
            $downloadoptions,
            'downloadformat',
            '',
            ['' => get_string('selectadownload', 'report_coursereport')],
            ['id' => 'downloadformat']
        );

        $downloadbutton = \html_writer::tag('button', get_string('download', 'report_coursereport'), [
            'type' => 'button',
            'id' => 'download-button',
            'class' => 'btn btn-primary']);

        $downloadcontainer = \html_writer::div(
            $downloadbutton . $downloadselect,
            'download-container',
            ['style' => 'margin-bottom: 10px; display: flex; align-items: center;']
        );

        $js = <<<EOD
        <script>
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
        </script>
        EOD;

        echo $js;

        // Pagination bar.
        $pagingbar = new \paging_bar($totalcount, $page, $perpage, $baseurl);
        $pagingbarhtml = $this->render($pagingbar);

        // Information about the number of courses displayed.
        $currentfrom = $page * $perpage + 1;
        $currentto = min(($page + 1) * $perpage, $totalcount);
        $info = "Mostrando {$currentfrom}–{$currentto} de {$totalcount} cursos";
        $infohtml = \html_writer::div($info, 'pagination-info', ['style' => 'margin-bottom: 10px; font-weight: bold;']);

        // Building HTML for courses.
        $courseshtml = '';
        if (!empty($courses)) {
            $courseshtml = \html_writer::start_tag('table', ['class' => 'generaltable table table-bordered',
                'style' => 'width: 100%; margin-top: 10px; border-collapse: collapse;']);
            $header = \html_writer::start_tag('thead');
            $header .= \html_writer::start_tag('tr');
            $header .= \html_writer::tag('th', 'Nombre Corto',
                ['scope' => 'col', 'style' => 'border-bottom: 2px solid #ccc; padding: 8px;']);
            $header .= \html_writer::tag('th', 'Nombre Largo',
                ['scope' => 'col', 'style' => 'border-bottom: 2px solid #ccc; padding: 8px;']);
            $header .= \html_writer::end_tag('tr');
            $header .= \html_writer::end_tag('thead');
            $courseshtml .= $header;

            $body = \html_writer::start_tag('tbody');
            foreach ($courses as $course) {
                $body .= \html_writer::start_tag('tr');
                $body .= \html_writer::tag('td', htmlspecialchars($course->shortname, ENT_QUOTES, 'UTF-8'),
                    ['style' => 'padding: 8px; border-bottom: 1px solid #eee;']);
                $body .= \html_writer::tag('td', htmlspecialchars($course->fullname, ENT_QUOTES, 'UTF-8'),
                    ['style' => 'padding: 8px; border-bottom: 1px solid #eee;']);
                $body .= \html_writer::end_tag('tr');
            }
            $body .= \html_writer::end_tag('tbody');
            $courseshtml .= $body;
            $courseshtml .= \html_writer::end_tag('table');
        } else {
            $courseshtml .= $this->output->notification("No hay cursos que coincidan con los criterios de búsqueda.",
                'notifymessage');
        }

        // Concatenating total HTML.
        return $infohtml . $pagingbarhtml . $courseshtml . $pagingbarhtml
        .$downloadbuttoncsv.$downloadbuttonexcel. $downloadcontainer;
    }
}
