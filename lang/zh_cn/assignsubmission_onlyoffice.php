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
$string['assignformat'] = '格式（ONLYOFFICE）';
$string['enablecomment'] = '启用学生评论';
$string['enablecomment:label'] = '学生对已评分文件的反馈';
$string['enablecomment_help'] = 'Если эта функция включена, учащиеся смогут оставлять комментарии непосредственно в документе ONLYOFFICE после того, как вы оцените их работу.';
$string['enabled'] = 'ONLYOFFICE 文档';
$string['enabled_help'] = 'Если эта функция включена, студенты могут отправлять документы ONLYOFFICE прямо в браузере. Вы можете задать формат отправляемого файла или создать шаблон для студентов.

For more information, visit <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">Help Center</a>.';
$string['filenotfound'] = '文件未找到';
$string['formnotready'] = '表单未准备完毕。请稍候并刷新页面。';
$string['hassubmissionswarning'] = '学生提交作业后，文件模板将无法修改。';
$string['mentionmessage'] = '{$a->type} <b>{$a->name}</b>已评论<b>{$a->document}</b>。如需查看，请打开该文件。';
$string['mentionmessage:gotofile'] = 'Перейдите к <a href="{$a->url}">отправке задания</a>, чтобы проверить комментарии';
$string['mentionsubject'] = '来自 {$a->type} 的新评论';
$string['messageprovider:submissioncommentnotifier'] = 'ONLYOFFICE 提交文档评论通知';
$string['pdfformname'] = '表单';
$string['pluginname'] = 'ONLYOFFICE 文档';
$string['templatetype'] = '文件模板 (ONLYOFFICE)';
$string['templatetype:custom'] = 'Изменить шаблон по умолчанию';
$string['templatetype:empty'] = 'Начать с пустого документа';
$string['templatetype_help'] = '此设置决定文件初始为空还是包含预定义内容。可用选项包括：<br/><ul class="template-type-help"><li>空：文件为空白，学生可以编辑所有文件类型（DOCX、XLSX、PPTX、PDF）。</li><li>自定义：您可以预定义文件内容。学生可以编辑 DOCX、XLSX 和 PPTX 文件，但 PDF 文件仅可供填写，不可编辑。</li></ul>';
$string['viewdocument'] = '查看 ONLYOFFICE 文档';
