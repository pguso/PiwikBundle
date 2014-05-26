<?php

namespace CiscoSystems\PiwikBundle\Model;

//use Buzz\Browser;
use GuzzleHttp\Client;

/**
 * derived from: https://github.com/VisualAppeal/Piwik-PHP-API
 */
class Connection
{

    const PERIOD_DAY        = 'day';
    const PERIOD_WEEK       = 'week';
    const PERIOD_MONTH      = 'month';
    const PERIOD_YEAR       = 'year';
    const PERIOD_RANGE      = 'range';

    const DATE_TODAY        = 'today';
    const DATE_YESTERDAY    = 'yesterday';

    const FORMAT_XML        = 'xml';
    const FORMAT_JSON       = 'json';
    const FORMAT_CSV        = 'csv';
    const FORMAT_TSV        = 'tsv';
    const FORMAT_HTML       = 'html';
    const FORMAT_PHP        = 'php';
    const FORMAT_RSS        = 'rss';
    const FORMAT_ORIGINAL   = 'original';

    protected $site         = '';
    protected $token        = '';
    protected $siteId       = 0;
    protected $format       = self::FORMAT_PHP;
//    protected $formats      = array(
//        self::FORMAT_CSV,
//        self::FORMAT_HTML,
//        self::FORMAT_JSON,
//        self::FORMAT_ORIGINAL,
//        self::FORMAT_PHP,
//        self::FORMAT_RSS,
//        self::FORMAT_TSV,
//        self::FORMAT_XML
//    );
    protected $language     = 'en';
    protected $period       = self::PERIOD_DAY;
//    protected $periods      = array(
//        self::PERIOD_DAY,
//        self::PERIOD_MONTH,
//        self::PERIOD_WEEK,
//        self::PERIOD_YEAR
//    );
    protected $date         = '';
    protected $rangeStart   = self::DATE_YESTERDAY;
    protected $rangeEnd     = null;
    protected $limit        = '';
    protected $errors       = array();
    protected $charset      = 'utf-8';
    protected $mimeType     = '';

    function __construct(
            $site,
            $token,
            $siteId,
            $format = self::FORMAT_JSON,
            $period = self::PERIOD_DAY,
            $date = self::DATE_YESTERDAY,
            $rangeStart = '',
            $rangeEnd = null
    )
    {
        $this->site         = $site;
        $this->token        = $token;
        $this->siteId       = $siteId;
        $this->format       = $format;
        $this->period       = $period;
        $this->rangeStart   = $rangeStart;
        $this->rangeEnd     = $rangeEnd;

        !empty( $rangeStart ) ?
            $this->setRange( $rangeStart, $rangeEnd ) :
            $this->setDate( $date );
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite( $url )
    {
        $this->site = $url;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken( $token )
    {
        $this->token = $token;
    }

    public function getSiteId()
    {
        return intval( $this->siteId );
    }

    public function setSiteId( $id )
    {
        $this->siteId = $id;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat( $format )
    {
        $this->format = $format;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage( $language )
    {
        $this->language = $language;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate( $date )
    {
        $this->date = $date;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod( $period )
    {
        $this->period = $period;
    }

    public function getRange()
    {
        return 'from ' . $this->rangeStart . ' until '
                . ( ( empty( $this->rangeEnd ) ) ? 'today' : $this->rangeEnd );
    }

    public function setRange( $rangeStart, $rangeEnd = null )
    {
        $this->date = '';
        $this->rangeStart = $rangeStart;
        $this->rangeEnd = $rangeEnd;

        if( is_null( $rangeEnd ) )
        {
            $this->date = $rangeStart;
        }
    }

    public function getLimit()
    {
        return intval( $this->limit );
    }

    public function setLimit( $limit )
    {
        $this->limit = $limit;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function reset()
    {
        $this->period = self::PERIOD_DAY;
        $this->date = '';
        $this->rangeStart = 'yesterday';
        $this->rangeEnd = null;

        $this->errors = array();
    }

    public function getModule( $name )
    {
        return new Module( $this, $name );
    }

    public function request( $method, $params = array() )
    {
        $params = count( $params ) > 0 ? $params[0] : $params;
        $query['query'] = array_merge( $params, array(
//            'module'        => 'Foo',
            'module'        => 'API',
            'method'        => $method,
            'token_auth'    => $this->token,
            'idSite'        => $this->siteId,
            'period'        => $this->period,
            'format'        => $this->format,
            'language'      => $this->language,
            'date'          => (( $this->period != self::PERIOD_RANGE ) ?
                                $this->date :
                                $this->rangeStart . ',' . $this->rangeEnd)
            ));

        $client = new Client();
        $response = $client->get( $this->site, $query );
        // test
//        $client = new Client([ 'base_url' => 'http://en.wikipedia.org' ]);
//        $response = $client->get( '/wiki/Henry_Ford' );
//        $response = $client->get( 'http://www.google.com/search?hl=en&q=cake' );
        $body = $response->getBody();
        $this->getContentType( $response );

        ladybug_dump_die(
                $response->getHeader( 'content-type' ),
                $response->getEffectiveUrl(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $body->getSize(), //258,658
                $body->seek( 0 ),
                $body->read( 258658 ),
                $this->html( $response )
                );

        try
        {
//            $response = $client->get( $this->site, $query );
//            $response = $client->get( 'http://www.google.com/search?hl=en&q=cake' );

//            ladybug_dump_die( $response->getBody() );
//            ladybug_dump_die( $response->getBody()->getContents() );
//            ladybug_dump_die( $response->getEffectiveUrl(), $response->getBody()->read( 5 ) );

            $json = $response->json();
            $status = $response->getStatusCode();

        }
        catch( ClientErrorResponseException  $e )
        {
            $this->addError( $e->getRequest() );
            $this->addError( $e->getResponse() );
        }

        return !$this->hasError() ? $json['value'] : $this->getErrors();
    }

    private function html( $response )
    {
        $size = $response->getBody()->getSize();
        $response->getBody()->seek( 0 );

        return $response->getBody()->read( $size );
    }

    private function getContentType( $response )
    {
        list( $this->mimeType, $after ) = explode( '; ',  $response->getHeader( 'content-type' ));
        list( $before, $this->charset ) = explode( '=', $after, 2 );

        return array(
            'mimeType'  => $this->mimeType,
            'charset'   => $this->charset
        );
    }

    private function addError( $msg = '' )
    {
        $this->errors = $this->errors + array( $msg );
    }

    public function hasError()
    {
        return ( count( $this->errors ));
    }

    public function getErrors()
    {
        return $this->errors;
    }
}