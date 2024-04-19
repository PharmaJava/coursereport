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


class descargaarchivos {

    public static function download_csv($courses) {
        // Asegura que no hay salida antes de enviar headers
        ob_start();
    
        // Configura los headers adecuados para descarga de archivo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="cursos.csv"');
    
        // Abre la salida de PHP directamente al flujo de salida para evitar uso de memoria excesivo
        $output = fopen('php://output', 'w');
        if (!$output) {
            ob_end_clean(); // Limpia el buffer y deshabilita
            die('No se pudo abrir el flujo de salida');
        }
    
        // Agrega BOM para UTF-8 si necesario
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
        // Define las cabeceras de las columnas en el CSV
        $headers = array('Nombre del Curso', 'Fecha de Inicio', 'Fecha de Finalización');
        fputcsv($output, $headers);
    
        // Itera sobre cada curso y escribe los datos en el archivo CSV
        foreach ($courses as $course) {
            $row = array(
                $course->name,
                date('d-m-Y', $course->startdate),
                date('d-m-Y', $course->enddate)
            );
            fputcsv($output, $row);
        }
    
        // Cierra el flujo de salida y limpia el buffer
        fclose($output);
        ob_end_flush();
    }
    

    public static function download_excel($courses) {
        global $CFG;
        require_once($CFG->libdir . '/excellib.class.php');
    
        // Configura los headers adecuados para la descarga del archivo Excel
        $filename = "cursos_exportados_" . userdate(time(), '%d%m%Y') . ".xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header("Cache-Control: max-age=0");
    
        // Crea una instancia de MoodleExcelWorkbook
        $workbook = new \MoodleExcelWorkbook("-");
        $workbook->send($filename);
    
        // Añade una hoja de cálculo
        $worksheet = $workbook->add_worksheet(get_string('cursos', 'report_coursereport'));
    
        // Define las cabeceras para las columnas
        $headers = array('CursoID', 'Nombre Corto', 'Nombre Largo', 'Fecha Inicio', 'Fecha Fin');
        $row = 0;
        $col = 0;
    
        // Escribe las cabeceras en la primera fila
        foreach ($headers as $header) {
            $worksheet->write_string($row, $col++, $header);
        }
    
        // Escribe los datos de los cursos
        $row = 1;
        foreach ($courses as $course) {
            $col = 0;
            $worksheet->write_string($row, $col++, $course->id);
            $worksheet->write_string($row, $col++, $course->shortname);
            $worksheet->write_string($row, $col++, $course->fullname);
            $worksheet->write_string($row, $col++, userdate($course->startdate, get_string('strftimedate', 'langconfig')));
            $worksheet->write_string($row, $col++, userdate($course->enddate, get_string('strftimedate', 'langconfig')));
            $row++;
        }
    
        // Cierra el libro de Excel y envía el archivo
        $workbook->close();
    }

}    

// Lógica para manejar la entrada y decidir qué acción tomar
$action = required_param('action', PARAM_ALPHA);

switch ($action) {
    case 'csv':
        $courses = fetch_courses_by_search($shortname, $fullname, $offset, $perpage);
        descargaarchivos::download_csv($courses);
        break;
    case 'excel':
        $courses = fetch_courses_by_search($shortname, $fullname, $offset, $perpage);
        descargaarchivos::download_excel($courses);
        break;
    default:
        throw new \moodle_exception('invalidaction', 'error');
}

exit;