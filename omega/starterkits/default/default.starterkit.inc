name = Default
description = Comes with a well organized Sass setup with heavy use of partials.
screenshot = screenshot.png
engine = phptemplate
core = 7.x

; Styles
stylesheets[all][] = css/{{ THEME SANITIZED }}.normalize.css
stylesheets[all][] = css/{{ THEME SANITIZED }}.hacks.css
stylesheets[all][] = css/{{ THEME SANITIZED }}.styles.css
stylesheets[all][] = css/{{ THEME SANITIZED }}.no-query.css

; Scripts
scripts[] = js/{{ THEME SANITIZED }}.behaviors.js

; Regions
regions[header]         = Header
regions[navigation]     = Navigation
regions[highlighted]    = Highlighted
regions[help]           = Help
regions[content]        = Content
regions[sidebar_first]  = First Sidebar
regions[sidebar_second] = Second Sidebar
regions[footer]         = Footer
