<?php
/**
 * Lights functions
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2013 All Rights Reserved
 * @version   git: $Id$
 */
namespace AB\Chroma;

class Lights extends Collection {
  private $hue_interface;
  private $bridge_ip = '192.168.10.30';

  public function __construct() {
    $this->hue_interface = Hue::get_instance();
    $this->load();
    usort($this->models, [ $this, 'light_name_compare' ]);
  }

  private function light_name_compare($a, $b) {
    return strcmp($a->name, $b->name);
  }

  public function set_state(Array $state, $light_id = 0) {
    if ($light_id == 0) {
      $success = true;
      foreach ($this as $light) {
        $ret = $this->hue_interface->set_light_state($light->id, $state);
        if (!$ret) {
          $success = false;
          break;
        }
        usleep(100000);
      }
      return $success;
    } else {
      return $this->hue_interface->set_light_state($light_id, $state);
    }
  }

  public function as_array() {
    $lights_array = [];

    // Create an array of each of the lights converted to an array. Simple.
    foreach ($this->models as $light) {
      $lights_array[] = $light->as_array();
    }

    return $lights_array;
  }

  public function load() {
    $response = $this->hue_interface->get_lights();

    foreach($response as $light_id => $light_data) {
      $light = new Light();
      $light->id = $light_id;
      $light->populate($light_data);
      $this->models[] = $light;
    }
  }
}
