<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This defines a structured class to hold question choices.
 *
 * @author    Mike Churchward
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package   response
 * @copyright 2019, onwards Poet
 */

namespace mod_feedbackbox\question\choice;

use coding_exception;
use dml_exception;

defined('MOODLE_INTERNAL') || die();

class choice {

    // Class properties.

    /** The table name. */
    const TABLE = 'feedbackbox_quest_choice';

    /** @var int $id The id of the question choice this applies to. */
    public $id;

    /** @var int $questionid The id of the question this choice applies to. */
    public $questionid;

    /** @var string $content The display content for this choice. */
    public $content;

    /** @var string $value Optional value assigned to this choice. */
    public $value;

    /**
     * Choice constructor.
     *
     * @param null $id
     * @param null $questionid
     * @param null $content
     * @param null $value
     */
    public function __construct($id = null, $questionid = null, $content = null, $value = null) {
        $this->id = $id;
        $this->questionid = $questionid;
        $this->content = $content;
        $this->value = $value;
    }

    /**
     * Create and return a choice object from a data id. If not found, an empty object is returned.
     *
     * @param int $id The data id to load.
     * @return choice
     * @throws dml_exception
     */
    public static function create_from_id($id) {
        global $DB;

        // Rename the data field question_id to questionid to conform with code conventions. Eventually, data table should be
        // changed.
        if ($record = $DB->get_record(self::tablename(), ['id' => $id], 'id,question_id as questionid,content,value')) {
            return new choice($id, $record->questionid, $record->content, $record->value);
        } else {
            return new choice();
        }
    }

    /**
     * Return the table name for choice.
     */
    public static function tablename() {
        return self::TABLE;
    }

    /**
     * Create and return a choice object from data.
     *
     * @param object | array $choicedata The data to load.
     * @return choice
     */
    public static function create_from_data($choicedata) {
        if (!is_array($choicedata)) {
            $choicedata = (array) $choicedata;
        }

        $properties = array_keys(get_class_vars(__CLASS__));
        foreach ($properties as $property) {
            if (!isset($choicedata[$property])) {
                $choicedata[$property] = null;
            }
        }
        // Since the data table uses 'question_id' instead of 'questionid', look for that field as well. Hack that should be fixed
        // by renaming the data table column.
        if (!empty($choicedata['question_id'])) {
            $choicedata['questionid'] = $choicedata['question_id'];
        }

        return new choice($choicedata['id'], $choicedata['questionid'], $choicedata['content'], '');
    }

    /**
     * Return true if the choice object is an "other" choice.
     *
     * @return bool
     */
    public function is_other_choice() {
        return (self::content_is_other_choice($this->content));
    }

    /**
     * Return true if the content string is an "other" choice.
     *
     * @param string $content
     * @return bool
     */
    static public function content_is_other_choice($content) {
        return (strpos($content, '!other') === 0);
    }

    /**
     * Return the string to display for an "other" option for this object. If the option is not an "other", return false.
     *
     * @return string | bool
     * @throws coding_exception
     */
    public function other_choice_display() {
        return self::content_other_choice_display($this->content);
    }

    /**
     * Return the string to display for an "other" option content string. If the option is not an "other", return false.
     *
     * @param $content
     * @return string | bool
     * @throws coding_exception
     */
    static public function content_other_choice_display($content) {
        if (!self::content_is_other_choice($content)) {
            return false;
        }

        // If there is a defined string display after the "=", return it. Otherwise the "other" language string.
        return preg_replace(["/^!other=/", "/^!other/"], ['', get_string('other', 'feedbackbox')], $content);
    }

    /**
     * Return the string to use as an input name for an other choice.
     *
     * @return string
     */
    public function other_choice_name() {
        return self::id_other_choice_name($this->id);
    }

    /**
     * Return the string to use as an input name for an other choice.
     *
     * @param int $choiceid
     * @return string
     */
    static public function id_other_choice_name($choiceid) {
        return 'o' . $choiceid;
    }
}