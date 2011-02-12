<?php

// fix messages 
// this should NOT be an empty string if it's freaking empty
if (isset($vars['messages']) && strlen($vars['messages']) == 0) {
  unset($vars['messages']);
}

// unset the action links array if it's empty
if (!$vars['action_links']) {
  unset($vars['action_links']);
}