<?php
/** no direct access **/
defined('MECEXEC') or die();

if(in_array($this->style, ['clean', 'modern'])) $calendar_type = 'calendar_clean';
elseif(in_array($this->style, ['novel'])) $calendar_type = 'calendar_novel';
elseif(in_array($this->style, ['simple'])) $calendar_type = 'calendar_simple';
else $calendar_type = 'calendar';

echo $this->draw_monthly_calendar($this->year, $this->month, $this->events, $calendar_type);