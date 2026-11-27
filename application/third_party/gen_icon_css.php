<?php
/*
 | SHULETECH UI - generates backend/dist/css/shuletech-icons.css
 | Redraws Font Awesome icons (fa-*) with the matching Lucide glyph, so no markup/JS has to change.
 | Run:  C:\xampp\php\php.exe application\third_party\gen_icon_css.php
 */
if (PHP_SAPI !== 'cli') { exit('CLI only'); }
$root = realpath(__DIR__ . '/../../') . '/';

// lucide glyph codepoints
$lucide = [];
preg_match_all('/\.icon-([a-z0-9-]+)::before\s*\{\s*content:\s*"\\\\([0-9a-f]+)"/i',
    file_get_contents($root . 'backend/dist/css/lucide-static@0.543.0/lucide.css'), $m, PREG_SET_ORDER);
foreach ($m as $x) { $lucide[$x[1]] = $x[2]; }

// fa name => lucide name
$map = [
 'search'=>'search','search-plus'=>'zoom-in','spinner'=>'loader','circle-o-notch'=>'loader-circle','refresh'=>'refresh-cw',
 'remove'=>'x','close'=>'x','times'=>'x','times-circle'=>'circle-x','times-circle-o'=>'circle-x',
 'pencil'=>'pencil','edit'=>'square-pen','pencil-square'=>'square-pen','pencil-square-o'=>'square-pen',
 'plus'=>'plus','plus-square-o'=>'square-plus','plus-circle'=>'circle-plus','minus'=>'minus','minus-circle'=>'circle-minus',
 'money'=>'banknote','usd'=>'dollar-sign','dollar'=>'dollar-sign','credit-card'=>'credit-card','calculator'=>'calculator',
 'users'=>'users','user'=>'user','user-plus'=>'user-plus','user-secret'=>'user-cog','user-circle'=>'circle-user','user-md'=>'stethoscope',
 'download'=>'download','upload'=>'upload','cloud-upload'=>'cloud-upload','cloud-download'=>'cloud-download',
 'file'=>'file','file-o'=>'file','file-text'=>'file-text','file-text-o'=>'file-text','files-o'=>'files','copy'=>'copy',
 'file-pdf-o'=>'file-text','file-excel-o'=>'file-spreadsheet','file-archive-o'=>'file-archive','file-video-o'=>'file-play',
 'file-image-o'=>'file-image','file-word-o'=>'file-text','folder'=>'folder','folder-open'=>'folder-open','folder-o'=>'folder',
 'print'=>'printer','save'=>'save','floppy-o'=>'save','trash'=>'trash-2','trash-o'=>'trash-2','undo'=>'undo-2',
 'chevron-left'=>'chevron-left','chevron-right'=>'chevron-right','chevron-up'=>'chevron-up','chevron-down'=>'chevron-down',
 'angle-left'=>'chevron-left','angle-right'=>'chevron-right','angle-up'=>'chevron-up','angle-down'=>'chevron-down',
 'angle-double-up'=>'chevrons-up','angle-double-down'=>'chevrons-down','angle-double-left'=>'chevrons-left','angle-double-right'=>'chevrons-right',
 'arrow-left'=>'arrow-left','arrow-right'=>'arrow-right','arrow-up'=>'arrow-up','arrow-down'=>'arrow-down','arrows'=>'move',
 'caret-down'=>'chevron-down','caret-up'=>'chevron-up','caret-right'=>'chevron-right','caret-left'=>'chevron-left',
 'exchange'=>'arrow-left-right','bus'=>'bus','plane'=>'plane','car'=>'car',
 'reorder'=>'menu','bars'=>'menu','navicon'=>'menu','th'=>'layout-grid','th-large'=>'layout-grid','th-list'=>'list',
 'list'=>'list','list-alt'=>'list-checks','list-ol'=>'list-ordered','list-ul'=>'list','columns'=>'columns-3',
 'clock-o'=>'clock','hourglass-start'=>'hourglass','calendar'=>'calendar','calendar-o'=>'calendar',
 'calendar-check-o'=>'calendar-check','calendar-plus-o'=>'calendar-plus','calendar-times-o'=>'calendar-x','calendar-minus-o'=>'calendar-minus',
 'check'=>'check','check-circle'=>'circle-check','check-circle-o'=>'circle-check','check-square'=>'square-check','check-square-o'=>'square-check',
 'square-o'=>'square','circle-o'=>'circle','circle'=>'circle','dot-circle-o'=>'circle-dot',
 'key'=>'key-round','lock'=>'lock','unlock'=>'lock-open','unlock-alt'=>'lock-open','shield'=>'shield',
 'picture-o'=>'image','image'=>'image','camera'=>'camera','video-camera'=>'video','television'=>'tv','youtube-play'=>'youtube',
 'mortar-board'=>'graduation-cap','graduation-cap'=>'graduation-cap','book'=>'book-open','flask'=>'flask-conical',
 'newspaper-o'=>'newspaper','envelope'=>'mail','envelope-o'=>'mail','at'=>'at-sign','paper-plane'=>'send','send'=>'send',
 'mail-reply'=>'reply','reply'=>'reply','share'=>'share','share-alt'=>'share-2',
 'phone'=>'phone','phone-square'=>'phone','mobile'=>'smartphone','whatsapp'=>'message-circle',
 'comment'=>'message-square','comment-o'=>'message-square','comments'=>'messages-square','comments-o'=>'messages-square','commenting-o'=>'message-square-text',
 'bell'=>'bell','bell-o'=>'bell','bullhorn'=>'megaphone','rss'=>'rss','wpforms'=>'clipboard-list',
 'info'=>'info','info-circle'=>'info','question'=>'circle-question-mark','question-circle'=>'circle-question-mark',
 'warning'=>'triangle-alert','exclamation-triangle'=>'triangle-alert','exclamation-circle'=>'circle-alert','exclamation'=>'circle-alert',
 'sitemap'=>'network','map-o'=>'map','map'=>'map','map-marker'=>'map-pin','globe'=>'globe','language'=>'languages',
 'building'=>'building-2','building-o'=>'building','home'=>'house','hospital-o'=>'hospital','bed'=>'bed-double',
 'gear'=>'settings','cog'=>'settings','gears'=>'settings-2','cogs'=>'settings-2','wrench'=>'wrench','tachometer'=>'gauge','dashboard'=>'layout-dashboard',
 'object-group'=>'boxes','cubes'=>'boxes','cube'=>'box','database'=>'database','server'=>'server',
 'line-chart'=>'chart-line','bar-chart'=>'chart-column','bar-chart-o'=>'chart-column','pie-chart'=>'chart-pie','area-chart'=>'chart-area',
 'star'=>'star','star-o'=>'star','heart'=>'heart','heart-o'=>'heart','smile-o'=>'smile','thumbs-o-up'=>'thumbs-up','thumbs-o-down'=>'thumbs-down',
 'tag'=>'tag','tags'=>'tags','shopping-cart'=>'shopping-cart','paperclip'=>'paperclip','link'=>'link',
 'eye'=>'eye','eye-slash'=>'eye-off','expand'=>'maximize-2','compress'=>'minimize-2','filter'=>'funnel','sort'=>'arrow-up-down',
 'ellipsis-v'=>'ellipsis-vertical','ellipsis-h'=>'ellipsis','sign-out'=>'log-out','sign-in'=>'log-in','power-off'=>'power',
 'ioxhost'=>'monitor-smartphone','empire'=>'globe','universal-access'=>'accessibility','id-card'=>'id-card','id-card-o'=>'id-card',
 'id-badge'=>'id-card','address-card'=>'contact','address-card-o'=>'contact','address-book'=>'book-user','certificate'=>'award','trophy'=>'trophy',
 'qrcode'=>'qr-code','barcode'=>'barcode','clipboard'=>'clipboard','calendar-check'=>'calendar-check','history'=>'history',
 'facebook'=>'facebook','twitter'=>'twitter','instagram'=>'instagram','linkedin'=>'linkedin','youtube'=>'youtube',
 'pinterest'=>'pin','google-plus'=>'at-sign','video'=>'video','music'=>'music','gift'=>'gift','money-bill'=>'banknote',
 'border-all'=>'square','border-none'=>'square-dashed','elementor'=>'panel-left','toggle-on'=>'toggle-right','toggle-off'=>'toggle-left',
];

$out  = "/*\n * SHULETECH UI: Font Awesome -> Lucide glyphs.\n * GENERATED by application/third_party/gen_icon_css.php - do not edit by hand.\n */\n";
$out .= ".fa,.fas,.far,.fa-solid,.fa-regular{font-weight:400!important;}\n";
$out .= ".fa:not(.fa-spin),.fas:not(.fa-spin),.far:not(.fa-spin){-webkit-font-smoothing:antialiased;}\n";
$missing = [];
$n = 0;
foreach ($map as $fa => $lu) {
    if (!isset($lucide[$lu])) { $missing[] = "$fa=>$lu"; continue; }
    $out .= ".fa-$fa::before{font-family:'lucide'!important;content:\"\\{$lucide[$lu]}\"!important;font-weight:400!important}\n";
    $n++;
}
// lucide strokes look smaller than FA at the same size; nudge up
$out .= ".fa[class*=\"fa-\"]::before{display:inline-block;transform:scale(1.08)}\n";
file_put_contents($root . 'backend/dist/css/shuletech-icons.css', $out);
echo "$n icons mapped; missing: " . implode(', ', $missing) . "\n";
