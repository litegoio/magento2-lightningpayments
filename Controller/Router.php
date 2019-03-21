<?php
namespace LiteGoio\LightningPayments\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{

    /**
    * @var \Magento\Framework\App\ActionFactory
    */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;


    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;        
        $this->_storeManager = $storeManager;
        $this->_response = $response;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');

        $condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);

        $identifier = $condition->getIdentifier();

        if ($condition->getRedirectUrl()) {
            $this->_response->setRedirect($condition->getRedirectUrl());
            $request->setDispatched(true);
            return $this->actionFactory->create('Magento\Framework\App\Action\Redirect');
        }

        if (!$condition->getContinue()) {
            return null;
        }

        $satisfy=preg_match("/checkout\/getorderstatus\/(.*)/iu",$identifier);
        if ($satisfy) {
            list($t,$t,$hash)=explode("/",$identifier);

            $request->setModuleName('checkout')->setControllerName('Litego')->setActionName('getOrderStatus')->setParam('hash', $hash);
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
        }

        $satisfy=preg_match("/checkout\/setlitego\/(.*)/iu",$identifier);
        if ($satisfy) {
            list($t,$t,$hash)=explode("/",$identifier);

            $request->setModuleName('checkout')->setControllerName('Litego')->setActionName('setLitego')->setParam('hash', $hash);
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
        }


        return null;
    }
}