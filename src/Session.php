<?php

namespace App;

/**
 * Class for running and working with session
 */
class Session
{
    /**
     * Method 'start' running session  
     *
     * @return void
     */
    public function start(): void
    {
        session_start();
    }

    /**
     * Method 'setData' for settiong data in session
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setData(string $key, mixed $value) 
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Method 'getData' for getting data from session
     *
     * @param string $key
     * @return mixed
     */
    public function getData(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Method 'save' for saving session data
     *
     * @return void
     */
    public function save(): void 
    {
        session_write_close();
    }

    /**
     * Method 'flash' for remove data by key
     * return value
     *
     * @param string $key
     * @return mixed
     */
    public function flash(string $key): mixed
    {
        $value = $this->getData($key);
        $this->unset($key);
        return $value;
    }

    public function clearAll(): void
    {
        session_destroy();
    }

    /**
     * Method 'unset' for delete data by key
     *
     * @param string $key
     * @return void
     */
    private function unset(string $key)
    {
        unset($_SESSION[$key]);
    }
}