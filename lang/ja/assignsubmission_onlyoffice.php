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
$string['enablecomment'] = '採点後に学生がドキュメント内にコメントできるようにする';
$string['enablecomment:label'] = '採点済みONLYOFFICEファイルに対する学生のフィードバック';
$string['enablecomment_help'] = 'この機能を有効にすると、提出物を採点した後、学生はONLYOFFICEドキュメント内に直接コメントを残すことができます。';
$string['enabled'] = 'ONLYOFFICEの文書';
$string['enabled_help'] = '有効にすると、学生はブラウザ上で直接ONLYOFFICEドキュメントを提出できるようになります。提出ファイルの形式を設定したり、学生用のテンプレートを作成したりすることができます。

詳細については、 <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">ヘルプセンター</a>をご覧ください。';
$string['filenotfound'] = 'ファイルが見つかりません';
$string['formnotready'] = 'フォームの準備がまだできていません。 しばらくお待ちいただき、ページを再読み込みしてください。';
$string['hassubmissionswarning'] = '学生が作業を提出した後は、ファイルテンプレートを変更することはできません。';
$string['mentionmessage'] = '{$a->type} <b>{$a->name}</b> さんが <b>{$a->document}</b> にコメントを追加しました。確認するには、ファイルを開いてください。';
$string['mentionmessage:gotofile'] = '<a href="{$a->url}">課題提出</a>に進んでコメントを確認してください。';
$string['mentionsubject'] = '{$a->type} さんからの新しいコメント';
$string['messageprovider:submissioncommentnotifier'] = 'ONLYOFFICE提出ドキュメントのコメント通知';
$string['pdfformname'] = 'フォーム';
$string['pluginname'] = 'ONLYOFFICEの文書';
$string['templatetype'] = 'ファイルテンプレート（ONLYOFFICE）';
$string['templatetype:custom'] = 'デフォルトテンプレートを編集';
$string['templatetype:empty'] = '空白ドキュメントで始める';
$string['templatetype_help'] = 'この設定は、ファイルが空の状態から開始するか、またはあらかじめ定義された内容から開始するかを決定します。利用可能なオプションは次のとおりです。<br/><ul class="template-type-help"><li>空： ファイルは空白で、学生はすべてのファイルタイプ （DOCX、XLSX、PPTX、PDF） を編集できます。</li><li>カスタム： ファイルの内容を事前に定義できます。学生は DOCX、XLSX、および PPTX ファイルを編集できますが、PDF ファイルは記入のみ可能で、編集はできません。</li></ul>';
$string['uploadfile'] = 'ファイルをアップロード';
$string['viewdocument'] = 'ONLYOFFICE文書を見る';
