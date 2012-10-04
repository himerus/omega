Introduction to Sass (http://sass-lang.com/)
============================================
Sass makes CSS fun again. Sass is an extension of CSS3, adding nested rules,
variables, mixins, selector inheritance, and more. It’s translated to well-
formatted, standard CSS using the command line tool or a web-framework plugin.

Sass has two syntaxes. The new main syntax (as of Sass 3) is known as “SCSS”
(for “Sassy CSS”), and is a superset of CSS3’s syntax. This means that every
valid CSS3 stylesheet is valid SCSS as well. SCSS files use the extension .scss.

The second, older syntax is known as the indented syntax (or just “Sass”).
Inspired by Haml’s terseness, it’s intended for people who prefer conciseness
over similarity to CSS. Instead of brackets and semicolons, it uses the
indentation of lines to specify blocks. Although no longer the primary syntax,
the indented syntax will continue to be supported. Files in the indented syntax
use the extension .sass.

@see http://sass-lang.com/docs.html
  Please refer to the Sass documentation for further information about the
  syntax.

Introduction to Compass (http://compass-style.org/)
===================================================
Compass is an open-source CSS Authoring Framework.

@see http://compass-style.org/reference
  Please refer to the Compass documentation for further information on how to
  leverage the powerful Compass framework.

Compass extensions
==================
There are many extensions available for Compass. You can install and use as many
of them together or just a single one depending on your use-case. Good examples
for useful Compass extensions are "susy" (a responsive grid framework) or
"compass-rgbapng" (a rgba() .png file generator) but there are many more.

Setting up and using Sass and Compass
=====================================
Compass runs on any computer that has ruby installed.

@see http://www.ruby-lang.org/en/downloads
  For a tutorial on how to install ruby.

Once you got ruby installed you can easily install the required gems from the
command line:

$ gem update --system
$ gem install compass

Any additional library can be installed in the same way:
$ gem install compass-rgbapng
$ gem install susy

Once you have set up your environment you can navigation to the folder that
holds your config.rb file.

The config.rb file is the configuration file that helps Sass and Compass to
understand your environment. For example, it defines which folder your .scss
or .sass files are stored in or where the generated .css files should be output
to.

Executing the following command will constantly watch for any change in your
.scss files and re-compile them into .css:

$ compass watch

You can also clear and recompile your .css manually:

$ compass clear
$ compass compile