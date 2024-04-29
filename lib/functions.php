<?php
require_once(__DIR__ . "/db.php");
//This is going to be a helper for redirecting to our base project path since it's nested in another folder
//This MUST match the folder name exactly
$BASE_PATH = '/Project';

require(__DIR__ . "/flash_messages.php");

require(__DIR__ . "/safer_echo.php");

require(__DIR__ . "/sanitizers.php");

require(__DIR__ . "/user_helpers.php");

require(__DIR__ . "/duplicate_user_details.php");

require(__DIR__ . "/reset_session.php");

require(__DIR__ . "/get_url.php");

require(__DIR__ . "/render_functions.php");

require(__DIR__ . "/api_helper.php");

require(__DIR__ . "/favorites_functions.php");