<?php

namespace Drupal\dr_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\Request;

class DrTestController extends ControllerBase {
  public function content(Request $request) {
    $connection = Database::getConnection();

    // 1. Generate a unique name per request using microtime to avoid primary key collisions
    $unique_name = 'req_' . microtime(true) . '_' . mt_rand(1000, 9999);

    // 2. Perform a safe insert into key_value with a unique key name
    $connection->insert('key_value')
      ->fields([
        'collection' => 'dr_test_metrics',
        'name' => $unique_name,
        'value' => serialize(time()),
      ])
      ->execute();

    // 3. Fetch total count of DB writes in our collection
    $count = $connection->select('key_value', 'k')
      ->condition('collection', 'dr_test_metrics')
      ->countQuery()
      ->execute()
      ->fetchField();

    // 4. Render content
    $markup = '<div class="dr-test-container">';
    $markup .= '<p>Database transaction records logged: <strong>' . (int)$count . '</strong></p>';
    $markup .= '<p><a href="/dr-test" class="button">Reload to Trigger DB Activity</a></p>';
    $markup .= '</div>';

    return [
      '#markup' => $markup,
      '#cache' => ['max-age' => 0],
      '#attached' => [
        'library' => ['dr_test/dr_test_styles'],
      ],
    ];
  }
}
