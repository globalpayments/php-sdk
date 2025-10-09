<?php
namespace GlobalPayments\Api\Entities;

/**
 * Entity for managing iframe callbacks of hosted payment pages. these properties go in root of the 
 * Hosted Payment Page request.
 * Note: some of the comments are taken straight from the documentation
 */
class HPPDisplayConfiguration
{
    /**
     * Domain of the application hosting the iframe or WebView.
     * This will tell the HPP to post back the height and width any time it changes,
     * for example if an input warning is displayed.
     *
     * @var string
     */
    public $iframe_dimensions_domain;
    
    /**
     * Domain for posting back transaction responses.
     * This will tell the HPP to post back the transaction response to the parent frame or window.
     * The response is posted back as a name/value pair JSON string with the values Base64 encoded.
     *
     * @var string|null
     */
    public $iframe_response_domain;
    
    /**
     * Create a new HPPDisplayConfiguration instance.
     *
     * @param string $iframe_dimensions_domain Domain of the application hosting the iframe
     * @param string|null $iframe_response_domain Domain for posting back transaction responses
     */
    public function __construct(string $iframe_dimensions_domain, ?string $iframe_response_domain = null)
    {
        $this->iframe_dimensions_domain = $iframe_dimensions_domain;
        $this->iframe_response_domain = $iframe_response_domain;
    }
    
    /**
     * Convert the object to an array representation for the HPP requests.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'iframe_dimensions_domain' => $this->iframe_dimensions_domain
        ];
        
        if ($this->iframe_response_domain !== null) {
            $result['iframe_response_domain'] = $this->iframe_response_domain;
        }
        
        return $result;
    }
}
