<?php
// src/Nantarena/SiteBundle/Countdown/CountdownRegistration.php

namespace Nantarena\SiteBundle\Countdown;

use Symfony\Component\HttpFoundation\Response;

class CountdownRegistration
{
  public function displayCountdown(Response $response, $remainingDays)
  {
    $content = $response->getContent();

    $html = '<p>J-'.(int) $remainingDays.' avant l\'ouverture des inscriptions !</a>';



      $content = preg_replace(
          '@<div id="Inscriptions">.*?</div>@sU',
          '<div id="RegistrationCountdown"><ul><li>'.$html.'</li></ul></div>',
          $content
      );



    $response->setContent($content);

    return $response;
  }
}