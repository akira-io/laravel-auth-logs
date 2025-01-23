<?php

test('it can run the command', function () {
    $this->artisan('auth-logs:install')
        ->expectsOutput('All done')
        ->assertExitCode(0);
});