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
$string['assignformat'] = '形式 (ONLYOFFICE)';
$string['enablecomment'] = '学生のコメントを有効にする';
$string['enablecomment:label'] = '採点済みファイルに対する学生のフィードバック';
$string['enablecomment_help'] = 'Если эта функция включена, учащиеся смогут оставлять комментарии непосредственно в документе ONLYOFFICE после того, как вы оцените их работу.';
$string['enabled'] = 'ONLYOFFICEの文書';
$string['enabled_help'] = 'Если эта функция включена, студенты могут отправлять документы ONLYOFFICE прямо в браузере. Вы можете задать формат отправляемого файла или создать шаблон для студентов.

For more information, visit <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">Help Center</a>.
$string['filenotfound'] = 'ファイルが見つかりません';
$string['formnotready'] = 'フォームの準備がまだできていません。 しばらくお待ちいただき、ページを再読み込みしてください。';
$string['hassubmissionswarning'] = '学生が作業を提出した後は、ファイルテンプレートを変更することはできません。';
$string['mentionmessage'] = '{$a->type} <b>{$a->name}</b> さんが <b>{$a->document}</b> にコメントを追加しました。確認するには、ファイルを開いてください。';
$string['mentionmessage:gotofile'] = 'Перейдите к <a href="{$a->url}">отправке задания</a>, чтобы проверить комментарии';
$string['mentionsubject'] = '{$a->type} さんからの新しいコメント';
$string['messageprovider:submissioncommentnotifier'] = 'ONLYOFFICE提出ドキュメントのコメント通知';
$string['pdfformname'] = 'フォーム';
$string['pluginname'] = 'ONLYOFFICEの文書';
$string['templatetype'] = 'ファイルテンプレート（ONLYOFFICE）';
$string['templatetype:custom'] = 'Изменить шаблон по умолчанию';
$string['templatetype:empty'] = 'Начать с пустого документа';
$string['templatetype_help'] = 'この設定は、ファイルが空の状態から開始するか、またはあらかじめ定義された内容から開始するかを決定します。利用可能なオプションは次のとおりです。<br/><ul class="template-type-help"><li>空： ファイルは空白で、学生はすべてのファイルタイプ （DOCX、XLSX、PPTX、PDF） を編集できます。</li><li>カスタム： ファイルの内容を事前に定義できます。学生は DOCX、XLSX、および PPTX ファイルを編集できますが、PDF ファイルは記入のみ可能で、編集はできません。</li></ul>';
$string['viewdocument'] = 'ONLYOFFICE文書を見る';
