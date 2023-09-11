<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'mvclogin';

    /**
     * Database user
     * @var string
     */
    const DB_USER = 'root';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = false;

    /**
     * Secret key for hashing
     * @var boolean
     */
    const SECRET_KEY = 'aEXlqGa3zAbWOuppjo6IsLlARPSygKAF';


    /**
     * Gmail SMTP server
     */
    const PM_HOST = 'smtp.gmail.com';

    /**
     * Address email
     */
    const PM_USERNAME = 'budgetappemail23@gmail.com';

    /**
     * 16 character obtained from app password created
     */
    const PM_PASSWORD = 'lfqdhbmqbnuerkck';
}
