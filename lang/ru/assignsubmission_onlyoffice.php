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
 * Strings for component 'assignsubmission_onlyoffice', language 'en'.
 *
 * @package     assignsubmission_onlyoffice
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['assignformat'] = 'Формат (ONLYOFFICE)';
$string['enablecomment'] = 'Включить комментарии студентов';
$string['enablecomment:label'] = 'Обратная связь студентов по поводу оцененных файлов';
$string['enablecomment_help'] = 'Если эта функция включена, учащиеся смогут оставлять комментарии непосредственно в документе ONLYOFFICE после того, как вы оцените их работу.';
$string['enabled'] = 'Документ ONLYOFFICE';
$string['enabled_help'] = 'Если эта функция включена, студенты могут отправлять документы ONLYOFFICE прямо в браузере. Вы можете задать формат отправляемого файла или создать шаблон для студентов.

For more information, visit <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">Help Center</a>.';
$string['filenotfound'] = 'Файл не найден';
$string['formnotready'] = 'Форма еще не готова. Пожалуйста, немного подождите и перезагрузите страницу.';
$string['hassubmissionswarning'] = 'Шаблон файла не может быть изменен после того, как студенты отправили свою работу.';
$string['mentionmessage'] = '{$a->type} <b>{$a->name}</b> прокомментировал <b>{$a->document}</b>. Чтобы проверить, откройте файл.';
$string['mentionmessage:gotofile'] = 'Перейдите к <a href="{$a->url}">отправке задания</a>, чтобы проверить комментарии';
$string['mentionsubject'] = 'Новый комментарий от {$a->type}';
$string['messageprovider:submissioncommentnotifier'] = 'Уведомление о комментариях к документу, отправленному в ONLYOFFICE';
$string['pdfformname'] = 'Форма';
$string['pluginname'] = 'Документ ONLYOFFICE';
$string['templatetype'] = 'Шаблон файла (ONLYOFFICE)';
$string['templatetype:custom'] = 'Изменить шаблон по умолчанию';
$string['templatetype:empty'] = 'Начать с пустого документа';
$string['templatetype_help'] = 'Эта настройка определяет, будет ли файл изначально пустым или с определенным содержимым. Доступны следующие параметры: <br/><ul class="template-type-help"><li>Пустой: файл будет пустым, и студенты смогут редактировать все типы файлов (DOCX, XLSX, PPTX, PDF).</li><li>Пользовательский: вы можете заранее определить содержимое файла. Учащиеся смогут редактировать файлы DOCX, XLSX и PPTX, но файлы PDF будут доступны только для заполнения, а не для редактирования.</li></ul>';
$string['uploadfile'] = 'Загрузить файл';
$string['viewdocument'] = 'Просмотреть документ ONLYOFFICE';
