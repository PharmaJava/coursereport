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
namespace report_coursereport\classes;

require_once(__DIR__ . '/../../../config.php');

require_once($CFG->dirroot . '/report/coursereport/lib.php');
$PAGE->set_url(new \moodle_url('report/coursereport/classes/descargaarchivos.php'));
$context = \context_system::instance();

require_login();

$action = required_param('action', PARAM_ALPHANUMEXT);
$shortname = optional_param('shortname', '', PARAM_NOTAGS);
$fullname = optional_param('fullname', '', PARAM_NOTAGS);

global $SESSION;
/**
 * Class descargaarchivos
 * Descarga formatos de archivos.
 */
class descargaarchivos {
    /**
     * Descarga el archivo CSV con los datos de los cursos filtrados.
     */
    public static function download_csv() {
        global $SESSION;

        // Verifica si los datos necesarios están en la sesión.
        if (!isset($SESSION->coursereport_filter) || empty($SESSION->coursereport_filter)) {
            die('Datos de sesión no configurados o vacíos. Por favor, realice una búsqueda primero.');
        }

        // Prepara los headers para descargar el archivo CSV.
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="cursos.csv"');

        // Abre el flujo de salida para escribir el archivo CSV.
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Agrega BOM para UTF-8.

        // Encabezados del CSV.
        $headers = ['Nombre del Curso', 'Fecha de Inicio', 'Fecha de Finalización'];
        fputcsv($output, $headers);

        // Escribe los datos de los cursos en el archivo CSV.
        foreach ($SESSION->coursereport_filter as $course) {
            $row = [
                $course->shortname,
                date('d-m-Y', $course->startdate),
                date('d-m-Y', $course->enddate)];
            fputcsv($output, $row);
        }

        // Cierra el flujo de salida.
        fclose($output);
        exit;
    }

    /**
     * Descarga el archivo Excel con los datos de los cursos filtrados.
     */
    public static function download_excel() {
        global $CFG, $SESSION;
        require_once($CFG->libdir . '/excellib.class.php');

        if (!isset($SESSION->coursereport_filter) || empty($SESSION->coursereport_filter)) {
            die('Datos de sesión no configurados o vacíos. Por favor, realice una búsqueda primero.');
        }

        // Configura los headers adecuados para la descarga del archivo Excel.
        $filename = "cursos_exportados_" . userdate(time(), '%d%m%Y') . ".xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header("Cache-Control: max-age=0");

        // Crea una instancia de MoodleExcelWorkbook.
        $workbook = new \MoodleExcelWorkbook("-");
        $workbook->send($filename);

        // Añade una hoja de cálculo.
        $worksheet = $workbook->add_worksheet(get_string('cursos', 'report_coursereport'));

        // Define las cabeceras para las columnas.
        $headers = ['CursoID', 'Nombre Corto', 'Nombre Largo', 'Fecha Inicio', 'Fecha Fin'];
        $row = 0;
        $col = 0;

        // Escribe las cabeceras en la primera fila.
        foreach ($headers as $header) {
            $worksheet->write_string($row, $col++, $header);
        }

        // Escribe los datos de los cursos.
        $row = 1;
        foreach ($SESSION->coursereport_filter as $course) {
            $col = 0;
            $worksheet->write_string($row, $col++, $course->id);
            $worksheet->write_string($row, $col++, $course->shortname);
            $worksheet->write_string($row, $col++, $course->fullname);
            $worksheet->write_string($row, $col++, userdate($course->startdate, get_string('strftimedate', 'langconfig')));
            $worksheet->write_string($row, $col++, userdate($course->enddate, get_string('strftimedate', 'langconfig')));
            $row++;
        }

        // Cierra el libro de Excel y envía el archivo.
        $workbook->close();
    }

}

// Ejecuta la acción correspondiente.
switch ($action) {
    case 'csv':
        descargaarchivos::download_csv();
        break;
    case 'excel':
        descargaarchivos::download_excel();
        break;
    default:
        throw new moodle_exception('invalidaction', 'error');
}
