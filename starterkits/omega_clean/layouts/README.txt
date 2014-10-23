##########################################################################################
 _____                      _____ _         
|     |_____ ___ ___ ___   |   __|_|_ _ ___ 
|  |  |     | -_| . | .'|  |   __| | | | -_|
|_____|_|_|_|___|_  |__,|  |__|  |_|\_/|___|
                |___|                       
                    
##########################################################################################

##########################################################################################
##### Omega Five Theme for Drupal 7
##########################################################################################


Project Page:   http://drupal.org/project/omega
Documentation:  
Issue Queue:    http://drupal.org/project/issues/omega
Usage Stats:    http://drupal.org/project/usage/omega
Maintainer(s):  Jake Strawn
                http://drupal.org/user/159141
                http://twitter.com/himerus
##########################################################################################

Creating New Layouts Manually
=============================

In order to create a new layout manually, you can simply create a new empty file called
your_layout.json. You will then need to copy/paste the data from an existing layout
that you wish to use as a starting point for this layouts configuration. This will make
the configuration process much easier to provide the default values from a layout that 
would closely represent the one you are attempting to create. 

Once you have pasted the JSON data from your original layout, at the very top, change 
the name of the layout as demonstrated below.

FROM:
{
    "original_layout":{
        "all":{
            "header":{
......

TO: 
{
    "your_layout":{
        "all":{
            "header":{      
......

Save your_layout.json, and you may now visit the theme settings page for your theme
and configure your new layout, and select it for use as the default layout, or as a
layout for a specific node type or homepage. 