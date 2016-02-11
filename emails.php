<?php
include('libraries/Mailchimp.php');
use \DrewM\MailChimp\MailChimp;
$mailChimp = new MailChimp('27f9b677ffb5e53f3d000bfd19ca305f-us12');
$listData = $mailChimp->get('lists');
if ($listData) {
    $listId = $listData['lists'][0]['id'];
    $subscriberEmail = filter_input(INPUT_POST, 'email');
    $subscriber = $mailChimp->post("lists/$listId/members", [
        'email_address' => $subscriberEmail,
        'status' => 'subscribed',
    ]);
}
?>