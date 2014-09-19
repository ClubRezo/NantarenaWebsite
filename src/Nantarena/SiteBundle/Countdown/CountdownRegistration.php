<?php
// src/Nantarena/SiteBundle/Countdown/CountdownRegistration.php

namespace Nantarena\SiteBundle\Countdown;

use Symfony\Component\HttpFoundation\Response;

class CountdownRegistration
{
  public function displayCountdown(Response $response, $remainingDays)
  {
    $content = $response->getContent();

    $html = '<p>J-'.(int) $remainingDays.' avant l\'ouverture des inscriptions !</p>';



      $content = preg_replace(
          '@<div class="inscriptions">.*?</div>@sU',
          '<div class="registrationCountdown"><ul><li>'.$html.'</li></ul></div>',
          $content
      );



    $response->setContent($content);

    return $response;
  }
}