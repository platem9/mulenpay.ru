
<?php

namespace Drupal\mulen_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MulenPaymentController extends ControllerBase {

  public function callback(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    // TODO: Add logic to process the callback data, e.g., update order status.
    // You should also verify the request signature here for security.

    Drupal::logger('mulen_payment')->notice('Callback received: <pre>@data</pre>', ['@data' => print_r($data, TRUE)]);

    return new JsonResponse(['status' => 'ok']);
  }

}
