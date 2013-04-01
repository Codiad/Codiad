<!doctype html>

<head>
    <meta charset="utf-8">
    <title>CODIAD STYLE GUIDE</title>
    <link rel="stylesheet" href="themes/default/reset.css">
    <link rel="stylesheet" href="themes/default/fonts.css">
    <link rel="stylesheet" href="themes/default/screen.css">
    <style type="text/css">
        html { overflow: scroll; }
        body { width: 100%; margin: 0 auto; overflow: scroll; }
        td .icon { font-size: 30px; display: inline; padding-top: 0; margin-top: 0; }
        p { padding: 15px 0; margin: 0; font-weight: bold; }
        label { margin-top: 25px; }
        #container { width: 600px; margin: 50px auto; }
        .item-icon { float: right; }
    </style>
</head>

<body>

    <div id="container">

    <label>Form Fields</label>
    
    <p>Code:</p>
    
    <pre>&lt;input type=&quot;text&quot;&gt;
    
&lt;select&gt;
    &lt;option value=&quot;one&quot;&lt;Option One&lt;/option&gt;
    &lt;option value=&quot;two&quot;&lt;Option Two&lt;/option&gt;
    &lt;option value=&quot;three&quot;&lt;Option Three&lt;/option&gt;
&lt;/select&gt;


&lt;textarea&gt;&lt;/textarea&gt;</pre>

    <p>Output:</p>
    
    <input type="text">
    
    <select>
        <option value="one">Option One</option>
        <option value="two">Option Two</option>
        <option value="three">Option Three</option>
    </select>
    
    <textarea></textarea>

    <label>Buttons</label>
    
    <p>Code:</p>
    
    <pre>&lt;button class=&quot;btn-left&quot;&gt;Left Button&lt;/button&gt;&lt;button class=&quot;btn-mid&quot;&gt;Mid Button&lt;/button&gt;&lt;button class=&quot;btn-right&quot;&gt;Right Button&lt;/button&gt;</pre>
    
    <p>Output:</p>
    
    <button class="btn-left">Left Button</button><button class="btn-mid">Mid Button</button><button class="btn-right">Right Button</button>
    
    
    <br><br>
    <label>Icons</label>

    <table style="font-weight: normal; width: 100%; margin: 0 auto;" cellpadding="5">
        <tr>
            <td>
icon-note                 : <span class="item-icon      icon-note           "></span><br>
icon-note-beamed          : <span class="item-icon      icon-note-beamed    "></span><br>
icon-music                : <span class="item-icon      icon-music          "></span><br>
icon-search               : <span class="item-icon      icon-search         "></span><br>
icon-flashlight           : <span class="item-icon      icon-flashlight     "></span><br>
icon-mail                 : <span class="item-icon      icon-mail           "></span><br>
icon-heart                : <span class="item-icon      icon-heart          "></span><br>
icon-heart-empty          : <span class="item-icon      icon-heart-empty    "></span><br>
icon-star                 : <span class="item-icon      icon-star           "></span><br>
icon-star-empty           : <span class="item-icon      icon-star-empty     "></span><br>
icon-user                 : <span class="item-icon      icon-user           "></span><br>
icon-users                : <span class="item-icon      icon-users          "></span><br>
icon-user-add             : <span class="item-icon      icon-user-add       "></span><br>
icon-video                : <span class="item-icon      icon-video          "></span><br>
icon-picture              : <span class="item-icon      icon-picture        "></span><br>
icon-camera               : <span class="item-icon      icon-camera         "></span><br>
icon-layout               : <span class="item-icon      icon-layout         "></span><br>
icon-menu                 : <span class="item-icon      icon-menu           "></span><br>
icon-check                : <span class="item-icon      icon-check          "></span><br>
icon-cancel               : <span class="item-icon      icon-cancel         "></span><br>
icon-cancel-circled       : <span class="item-icon      icon-cancel-circled "></span><br>
icon-cancel-squared       : <span class="item-icon      icon-cancel-squared "></span><br>
icon-plus                 : <span class="item-icon      icon-plus           "></span><br>
icon-plus-circled         : <span class="item-icon      icon-plus-circled   "></span><br>
icon-plus-squared         : <span class="item-icon      icon-plus-squared   "></span><br>
icon-minus                : <span class="item-icon      icon-minus          "></span><br>
icon-minus-circled        : <span class="item-icon      icon-minus-circled  "></span><br>
icon-minus-squared        : <span class="item-icon      icon-minus-squared  "></span><br>
icon-help                 : <span class="item-icon      icon-help           "></span><br>
icon-help-circled         : <span class="item-icon      icon-help-circled   "></span><br>
icon-info                 : <span class="item-icon      icon-info           "></span><br>
icon-info-circled         : <span class="item-icon      icon-info-circled   "></span><br>
icon-back                 : <span class="item-icon      icon-back           "></span><br>
icon-home                 : <span class="item-icon      icon-home           "></span><br>
icon-link                 : <span class="item-icon      icon-link           "></span><br>
icon-attach               : <span class="item-icon      icon-attach         "></span><br>
icon-lock                 : <span class="item-icon      icon-lock           "></span><br>
icon-lock-open            : <span class="item-icon      icon-lock-open      "></span><br>
icon-eye                  : <span class="item-icon      icon-eye            "></span><br>
icon-tag                  : <span class="item-icon      icon-tag            "></span><br>
icon-bookmark             : <span class="item-icon      icon-bookmark       "></span><br>
icon-bookmarks            : <span class="item-icon      icon-bookmarks      "></span><br>
icon-flag                 : <span class="item-icon      icon-flag           "></span><br>
icon-thumbs-up            : <span class="item-icon      icon-thumbs-up      "></span><br>
icon-thumbs-down          : <span class="item-icon      icon-thumbs-down    "></span><br>
icon-download             : <span class="item-icon      icon-download       "></span><br>
icon-upload               : <span class="item-icon      icon-upload         "></span><br>
icon-upload-cloud         : <span class="item-icon      icon-upload-cloud   "></span><br>
icon-reply                : <span class="item-icon      icon-reply          "></span><br>
icon-reply-all            : <span class="item-icon      icon-reply-all      "></span><br>
icon-forward              : <span class="item-icon      icon-forward        "></span><br>
icon-quote                : <span class="item-icon      icon-quote          "></span><br>
icon-code                 : <span class="item-icon      icon-code           "></span><br>
icon-export               : <span class="item-icon      icon-export         "></span><br>
icon-pencil               : <span class="item-icon      icon-pencil         "></span><br>
icon-feather              : <span class="item-icon      icon-feather        "></span><br>
icon-print                : <span class="item-icon      icon-print          "></span><br>
icon-retweet              : <span class="item-icon      icon-retweet        "></span><br>
icon-keyboard             : <span class="item-icon      icon-keyboard       "></span><br>
icon-comment              : <span class="item-icon      icon-comment        "></span><br>
icon-chat                 : <span class="item-icon      icon-chat           "></span><br>
icon-bell                 : <span class="item-icon      icon-bell           "></span><br>
icon-attention            : <span class="item-icon      icon-attention      "></span><br>
icon-alert                : <span class="item-icon      icon-alert          "></span><br>
icon-vcard                : <span class="item-icon      icon-vcard          "></span><br>
icon-address              : <span class="item-icon      icon-address        "></span><br>
icon-location             : <span class="item-icon      icon-location       "></span><br>
icon-map                  : <span class="item-icon      icon-map            "></span><br>
icon-direction            : <span class="item-icon      icon-direction      "></span><br>
icon-compass              : <span class="item-icon      icon-compass        "></span><br>
icon-cup                  : <span class="item-icon      icon-cup            "></span><br>
icon-trash                : <span class="item-icon      icon-trash          "></span><br>
icon-doc                  : <span class="item-icon      icon-doc            "></span><br>
icon-docs                 : <span class="item-icon      icon-docs           "></span><br>
icon-doc-landscape        : <span class="item-icon      icon-doc-landscape  "></span><br>
icon-doc-text             : <span class="item-icon      icon-doc-text       "></span><br>
icon-doc-text-inv         : <span class="item-icon      icon-doc-text-inv   "></span><br>
icon-newspaper            : <span class="item-icon      icon-newspaper      "></span><br>
icon-book-open            : <span class="item-icon      icon-book-open      "></span><br>
icon-book                 : <span class="item-icon      icon-book           "></span><br>
icon-folder               : <span class="item-icon      icon-folder         "></span><br>
icon-archive              : <span class="item-icon      icon-archive        "></span><br>
icon-box                  : <span class="item-icon      icon-box            "></span><br>
icon-rss                  : <span class="item-icon      icon-rss            "></span><br>
</td>
<td>
icon-phone                : <span class="item-icon      icon-phone          "></span><br>
icon-cog                  : <span class="item-icon      icon-cog            "></span><br>
icon-tools                : <span class="item-icon      icon-tools          "></span><br>
icon-share                : <span class="item-icon      icon-share          "></span><br>
icon-shareable            : <span class="item-icon      icon-shareable      "></span><br>
icon-basket               : <span class="item-icon      icon-basket         "></span><br>
icon-bag                  : <span class="item-icon      icon-bag            "></span><br>
icon-calendar             : <span class="item-icon      icon-calendar       "></span><br>
icon-login                : <span class="item-icon      icon-login          "></span><br>
icon-logout               : <span class="item-icon      icon-logout         "></span><br>
icon-mic                  : <span class="item-icon      icon-mic            "></span><br>
icon-mute                 : <span class="item-icon      icon-mute           "></span><br>
icon-sound                : <span class="item-icon      icon-sound          "></span><br>
icon-volume               : <span class="item-icon      icon-volume         "></span><br>
icon-clock                : <span class="item-icon      icon-clock          "></span><br>
icon-hourglass            : <span class="item-icon      icon-hourglass      "></span><br>
icon-lamp                 : <span class="item-icon      icon-lamp           "></span><br>
icon-light-down           : <span class="item-icon      icon-light-down     "></span><br>
icon-light-up             : <span class="item-icon      icon-light-up       "></span><br>
icon-adjust               : <span class="item-icon      icon-adjust         "></span><br>
icon-block                : <span class="item-icon      icon-block          "></span><br>
icon-resize-full          : <span class="item-icon      icon-resize-full    "></span><br>
icon-resize-small         : <span class="item-icon      icon-resize-small   "></span><br>
icon-popup                : <span class="item-icon      icon-popup          "></span><br>
icon-publish              : <span class="item-icon      icon-publish        "></span><br>
icon-window               : <span class="item-icon      icon-window         "></span><br>
icon-arrow-combo          : <span class="item-icon      icon-arrow-combo    "></span><br>
icon-down-circled         : <span class="item-icon      icon-down-circled   "></span><br>
icon-left-circled         : <span class="item-icon      icon-left-circled   "></span><br>
icon-right-circled        : <span class="item-icon      icon-right-circled  "></span><br>
icon-up-circled           : <span class="item-icon      icon-up-circled     "></span><br>
icon-down-open            : <span class="item-icon      icon-down-open      "></span><br>
icon-left-open            : <span class="item-icon      icon-left-open      "></span><br>
icon-right-open           : <span class="item-icon      icon-right-open     "></span><br>
icon-up-open              : <span class="item-icon      icon-up-open        "></span><br>
icon-down-open-mini       : <span class="item-icon      icon-down-open-mini "></span><br>
icon-left-open-mini       : <span class="item-icon      icon-left-open-mini "></span><br>
icon-right-open-mini      : <span class="item-icon      icon-right-open-mini"></span><br>
icon-up-open-mini         : <span class="item-icon      icon-up-open-mini   "></span><br>
icon-down-open-big        : <span class="item-icon      icon-down-open-big  "></span><br>
icon-left-open-big        : <span class="item-icon      icon-left-open-big  "></span><br>
icon-right-open-big       : <span class="item-icon      icon-right-open-big "></span><br>
icon-up-open-big          : <span class="item-icon      icon-up-open-big    "></span><br>
icon-down                 : <span class="item-icon      icon-down           "></span><br>
icon-left                 : <span class="item-icon      icon-left           "></span><br>
icon-right                : <span class="item-icon      icon-right          "></span><br>
icon-up                   : <span class="item-icon      icon-up             "></span><br>
icon-down-dir             : <span class="item-icon      icon-down-dir       "></span><br>
icon-left-dir             : <span class="item-icon      icon-left-dir       "></span><br>
icon-right-dir            : <span class="item-icon      icon-right-dir      "></span><br>
icon-up-dir               : <span class="item-icon      icon-up-dir         "></span><br>
icon-down-bold            : <span class="item-icon      icon-down-bold      "></span><br>
icon-left-bold            : <span class="item-icon      icon-left-bold      "></span><br>
icon-right-bold           : <span class="item-icon      icon-right-bold     "></span><br>
icon-up-bold              : <span class="item-icon      icon-up-bold        "></span><br>
icon-down-thin            : <span class="item-icon      icon-down-thin      "></span><br>
icon-left-thin            : <span class="item-icon      icon-left-thin      "></span><br>
icon-right-thin           : <span class="item-icon      icon-right-thin     "></span><br>
icon-up-thin              : <span class="item-icon      icon-up-thin        "></span><br>
icon-ccw                  : <span class="item-icon      icon-ccw            "></span><br>
icon-cw                   : <span class="item-icon      icon-cw             "></span><br>
icon-arrows-ccw           : <span class="item-icon      icon-arrows-ccw     "></span><br>
icon-level-down           : <span class="item-icon      icon-level-down     "></span><br>
icon-level-up             : <span class="item-icon      icon-level-up       "></span><br>
icon-shuffle              : <span class="item-icon      icon-shuffle        "></span><br>
icon-loop                 : <span class="item-icon      icon-loop           "></span><br>
icon-switch               : <span class="item-icon      icon-switch         "></span><br>
icon-play                 : <span class="item-icon      icon-play           "></span><br>
icon-stop                 : <span class="item-icon      icon-stop           "></span><br>
icon-pause                : <span class="item-icon      icon-pause          "></span><br>
icon-record               : <span class="item-icon      icon-record         "></span><br>
icon-to-end               : <span class="item-icon      icon-to-end         "></span><br>
icon-to-start             : <span class="item-icon      icon-to-start       "></span><br>
icon-fast-forward         : <span class="item-icon      icon-fast-forward   "></span><br>
icon-fast-backward        : <span class="item-icon      icon-fast-backward  "></span><br>
icon-progress-0           : <span class="item-icon      icon-progress-0     "></span><br>
icon-progress-1           : <span class="item-icon      icon-progress-1     "></span><br>
icon-progress-2           : <span class="item-icon      icon-progress-2     "></span><br>
icon-progress-3           : <span class="item-icon      icon-progress-3     "></span><br>
icon-target               : <span class="item-icon      icon-target         "></span><br>
icon-palette              : <span class="item-icon      icon-palette        "></span><br>
icon-list                 : <span class="item-icon      icon-list           "></span><br>
icon-list-add             : <span class="item-icon      icon-list-add       "></span><br>
icon-signal               : <span class="item-icon      icon-signal         "></span><br>
</td>
<td>
icon-trophy               : <span class="item-icon      icon-trophy         "></span><br>
icon-battery              : <span class="item-icon      icon-battery        "></span><br>
icon-back-in-time         : <span class="item-icon      icon-back-in-time   "></span><br>
icon-monitor              : <span class="item-icon      icon-monitor        "></span><br>
icon-mobile               : <span class="item-icon      icon-mobile         "></span><br>
icon-network              : <span class="item-icon      icon-network        "></span><br>
icon-cd                   : <span class="item-icon      icon-cd             "></span><br>
icon-inbox                : <span class="item-icon      icon-inbox          "></span><br>
icon-install              : <span class="item-icon      icon-install        "></span><br>
icon-globe                : <span class="item-icon      icon-globe          "></span><br>
icon-cloud                : <span class="item-icon      icon-cloud          "></span><br>
icon-cloud-thunder        : <span class="item-icon      icon-cloud-thunder  "></span><br>
icon-flash                : <span class="item-icon      icon-flash          "></span><br>
icon-moon                 : <span class="item-icon      icon-moon           "></span><br>
icon-flight               : <span class="item-icon      icon-flight         "></span><br>
icon-paper-plane          : <span class="item-icon      icon-paper-plane    "></span><br>
icon-leaf                 : <span class="item-icon      icon-leaf           "></span><br>
icon-lifebuoy             : <span class="item-icon      icon-lifebuoy       "></span><br>
icon-mouse                : <span class="item-icon      icon-mouse          "></span><br>
icon-briefcase            : <span class="item-icon      icon-briefcase      "></span><br>
icon-suitcase             : <span class="item-icon      icon-suitcase       "></span><br>
icon-dot                  : <span class="item-icon      icon-dot            "></span><br>
icon-dot-2                : <span class="item-icon      icon-dot-2          "></span><br>
icon-dot-3                : <span class="item-icon      icon-dot-3          "></span><br>
icon-brush                : <span class="item-icon      icon-brush          "></span><br>
icon-magnet               : <span class="item-icon      icon-magnet         "></span><br>
icon-infinity             : <span class="item-icon      icon-infinity       "></span><br>
icon-erase                : <span class="item-icon      icon-erase          "></span><br>
icon-chart-pie            : <span class="item-icon      icon-chart-pie      "></span><br>
icon-chart-line           : <span class="item-icon      icon-chart-line     "></span><br>
icon-chart-bar            : <span class="item-icon      icon-chart-bar      "></span><br>
icon-chart-area           : <span class="item-icon      icon-chart-area     "></span><br>
icon-tape                 : <span class="item-icon      icon-tape           "></span><br>
icon-graduation-cap       : <span class="item-icon      icon-graduation-cap "></span><br>
icon-language             : <span class="item-icon      icon-language       "></span><br>
icon-ticket               : <span class="item-icon      icon-ticket         "></span><br>
icon-water                : <span class="item-icon      icon-water          "></span><br>
icon-droplet              : <span class="item-icon      icon-droplet        "></span><br>
icon-air                  : <span class="item-icon      icon-air            "></span><br>
icon-credit-card          : <span class="item-icon      icon-credit-card    "></span><br>
icon-floppy               : <span class="item-icon      icon-floppy         "></span><br>
icon-clipboard            : <span class="item-icon      icon-clipboard      "></span><br>
icon-megaphone            : <span class="item-icon      icon-megaphone      "></span><br>
icon-database             : <span class="item-icon      icon-database       "></span><br>
icon-drive                : <span class="item-icon      icon-drive          "></span><br>
icon-bucket               : <span class="item-icon      icon-bucket         "></span><br>
icon-thermometer          : <span class="item-icon      icon-thermometer    "></span><br>
icon-key                  : <span class="item-icon      icon-key            "></span><br>
icon-flow-cascade         : <span class="item-icon      icon-flow-cascade   "></span><br>
icon-flow-branch          : <span class="item-icon      icon-flow-branch    "></span><br>
icon-flow-tree            : <span class="item-icon      icon-flow-tree      "></span><br>
icon-flow-line            : <span class="item-icon      icon-flow-line      "></span><br>
icon-flow-parallel        : <span class="item-icon      icon-flow-parallel  "></span><br>
icon-rocket               : <span class="item-icon      icon-rocket         "></span><br>
icon-gauge                : <span class="item-icon      icon-gauge          "></span><br>
icon-traffic-cone         : <span class="item-icon      icon-traffic-cone   "></span><br>
icon-cc                   : <span class="item-icon      icon-cc             "></span><br>
icon-cc-by                : <span class="item-icon      icon-cc-by          "></span><br>
icon-cc-nc                : <span class="item-icon      icon-cc-nc          "></span><br>
icon-cc-nc-eu             : <span class="item-icon      icon-cc-nc-eu       "></span><br>
icon-cc-nc-jp             : <span class="item-icon      icon-cc-nc-jp       "></span><br>
icon-cc-sa                : <span class="item-icon      icon-cc-sa          "></span><br>
icon-cc-nd                : <span class="item-icon      icon-cc-nd          "></span><br>
icon-cc-pd                : <span class="item-icon      icon-cc-pd          "></span><br>
icon-cc-zero              : <span class="item-icon      icon-cc-zero        "></span><br>
icon-cc-share             : <span class="item-icon      icon-cc-share       "></span><br>
icon-cc-remix             : <span class="item-icon      icon-cc-remix       "></span><br>
icon-rdio                 : <span class="item-icon      icon-rdio           "></span><br>
icon-rdio-circled         : <span class="item-icon      icon-rdio-circled   "></span><br>
icon-spotify              : <span class="item-icon      icon-spotify        "></span><br>
icon-spotify-circled      : <span class="item-icon      icon-spotify-circled"></span><br>
icon-qq                   : <span class="item-icon      icon-qq             "></span><br>
icon-renren               : <span class="item-icon      icon-renren         "></span><br>
icon-mixi                 : <span class="item-icon      icon-mixi           "></span><br>
icon-behance              : <span class="item-icon      icon-behance        "></span><br>
icon-vkontakte            : <span class="item-icon      icon-vkontakte      "></span><br>
icon-smashing             : <span class="item-icon      icon-smashing       "></span><br>
icon-sweden               : <span class="item-icon      icon-sweden         "></span><br>
icon-db-shape             : <span class="item-icon      icon-db-shape       "></span><br>
icon-logo-db              : <span class="item-icon      icon-logo-db        "></span><br>
            </td>
        </tr>
    </table>

    </div>

</body>
</html>
