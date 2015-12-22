<?php namespace CupOfTea\Support;

use CupOfTea\Package\Package;

class Support
{
    use Package;
    
    /**
     * Package Name.
     *
     * @const string
     */
    const PACKAGE = 'CupOfTea/Support';
    
    /**
     * Package Version.
     *
     * @const string
     */
    const VERSION = '0.0.0';
    
    /**
     * Lists the classes provided by this package.
     *
     * @return array
     */
    public static function provides()
    {
        return [
            Str::class,
            Wrapper::class
        ];
    }
}
