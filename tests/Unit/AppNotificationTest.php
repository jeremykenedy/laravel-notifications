<?php

use Jeremykenedy\LaravelNotifications\Notifications\AppNotification;

it('uses the database channel by default', function () {
    $notification = new AppNotification(title: 'Title', message: 'Message');

    expect($notification->via(new stdClass()))->toBe(['database']);
});

it('adds the mail channel when asked to send email', function () {
    $notification = new AppNotification(title: 'Title', message: 'Message', sendEmail: true);

    expect($notification->via(new stdClass()))->toBe(['database', 'mail']);
});

it('stores title message and type', function () {
    $data = (new AppNotification(title: 'Title', message: 'Message', type: 'warning'))->toArray(new stdClass());

    expect($data)->toBe(['title' => 'Title', 'message' => 'Message', 'type' => 'warning']);
});

it('omits the icon and action keys when they are not given', function () {
    $data = (new AppNotification(title: 'Title', message: 'Message'))->toArray(new stdClass());

    expect($data)->not->toHaveKey('icon')
        ->and($data)->not->toHaveKey('action_url')
        ->and($data)->not->toHaveKey('action_text');
});

it('defaults the action text when only a url is given', function () {
    $data = (new AppNotification(title: 'T', message: 'M', actionUrl: '/somewhere'))->toArray(new stdClass());

    expect($data['action_url'])->toBe('/somewhere')
        ->and($data['action_text'])->toBe('View');
});

it('builds a mail message with the action button', function () {
    $mail = (new AppNotification(title: 'Invoice', message: 'Ready.', actionUrl: '/i/1', actionText: 'Open'))
        ->toMail(new stdClass());

    expect($mail->subject)->toBe('Invoice')
        ->and($mail->introLines)->toContain('Ready.')
        ->and($mail->actionText)->toBe('Open')
        ->and($mail->actionUrl)->toBe('/i/1');
});

it('builds a mail message without an action button', function () {
    $mail = (new AppNotification(title: 'Plain', message: 'No link.'))->toMail(new stdClass());

    expect($mail->actionText)->toBeNull();
});
