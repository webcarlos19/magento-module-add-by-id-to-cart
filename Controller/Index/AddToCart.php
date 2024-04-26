<?php
namespace OceanDrop\Webhook\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;

class AddToCart extends Action
{
    protected $resultFactory;
    protected $cart;
    protected $productRepository;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Cart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        $this->resultFactory = $resultFactory;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        // Obtenha os IDs dos produtos enviados por JSON
        $productIds = $this->getRequest()->getParam('product_ids');
        $productIds = json_decode($productIds); // Converter para array se necessário

        try {
            // Adicionar cada produto ao carrinho
            foreach ($productIds as $productId) {
                $product = $this->productRepository->getById($productId);
                if ($product) {
                    $params = [
                        'product' => $productId,
                        'qty' => 1 // Quantidade a adicionar ao carrinho
                    ];
                    $this->cart->addProduct($product, $params);
                }
            }

            // Salvar o carrinho
            $this->cart->save();

            // Redirecionar para o checkout
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('checkout');
            return $resultRedirect;

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Erro ao adicionar produtos ao carrinho.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('checkout/cart'); // Redirecionar de volta ao carrinho em caso de erro
            return $resultRedirect;
        }
    }
}
