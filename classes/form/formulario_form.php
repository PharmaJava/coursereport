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

namespace report_coursereport\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class formulario_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // Nombre Corto.
        $mform->addElement('text', 'shortname', get_string('shortname', 'report_coursereport'));
        $mform->setType('shortname', PARAM_TEXT);

        // Nombre Largo.
        $mform->addElement('text', 'fullname', get_string('fullname', 'report_coursereport'));
        $mform->setType('fullname', PARAM_TEXT);

        // Botón de envío.
        $mform->addElement('submit', 'submitbutton', get_string('submit', 'report_coursereport'));
    }
}
