name = Extended
description = Comes with a well organized Sass setup with heavy use of partials.
screenshot = screenshot.png
engine = phptemplate
core = 7.x

; Styles
stylesheets[all][] = css/{{ THEME }}.normalize.css
stylesheets[all][] = css/{{ THEME }}.hacks.css
stylesheets[all][] = css/{{ THEME }}.styles.css

; Regions
regions[header]         = Header
regions[navigation]     = Navigation
regions[highlighted]    = Highlighted
regions[help]           = Help
regions[content]        = Content
regions[sidebar_first]  = First Sidebar
regions[sidebar_second] = Second Sidebar
regions[footer]         = Footer
