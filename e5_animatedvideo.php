<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class E5_AnimatedVideo extends Module
{

    public function __construct()
    {
        $this->name = 'e5_animatedvideo';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Valentin';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Animated Video for Element5 Products');
        $this->description = $this->l('This module allows you to add an animated video to products when hovering on the product image');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (
            !parent::install() || !$this->_installSql()
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('displayProductAnimatedVideo')
            || !$this->registerHook('displayHeader')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->_unInstallSql() && $this->deleteAllVid();
    }

    /**
     * Delete all videos in the folder views/videos when uninstalling the module
     * 
     * @see glob() : https://www.php.net/manual/fr/function.glob.php
     * @return boolean
     */
    protected function deleteAllVid() {
        //Delete all videos in the folder views/videos
        try {
            $dir = dirname(__FILE__) . '/views/videos/';
            //Get all files in the folder
            $files = glob($dir . '*', GLOB_MARK | GLOB_NOSORT);

            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    //if the file is not a php file, delete it
                    if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                        unlink($file);
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Modifications sql du module
     * @return boolean
     */
    protected function _installSql()
    {
        $sqlInstall = "ALTER TABLE " . _DB_PREFIX_ . "product "
            . "ADD video_name VARCHAR(255) NULL";

        $returnSql = Db::getInstance()->execute($sqlInstall);

        return $returnSql;
    }

    /**
     * Suppression des modification sql du module
     * @return boolean
     */
    protected function _unInstallSql()
    {
        $sqlInstall = "ALTER TABLE " . _DB_PREFIX_ . "product "
            . "DROP video_name";

        $returnSql = Db::getInstance()->execute($sqlInstall);

        return $returnSql;
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getContent()
    {
        //Gestion de l'envoi des fichiers
        if (Tools::getValue('uploadProductImage')) {
            $id_product = (int)Tools::getValue('id_product');
            $field_name = Tools::getValue('field_name');

            if ($this->_updateProductImageField($id_product, $field_name)) {
                return $this->l('Video uploaded with success');
            }
        } elseif (Tools::getValue('deleteProductImage')) {
            $id_product = (int)Tools::getValue('id_product');
            $field_name = Tools::getValue('field_name');
            if ($this->_deleteProductImageField($id_product, $field_name)) {
                return $this->l('Video deleted with success');
            }
        } else {
            return 'No configuration needed for this module';
        }
    }

    /**
     * Upload de l'image d'un produit
     * @param $id_product
     * @param $field_name
     * 
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function _updateProductImageField($id_product, $field_name)
    {
        $product = new Product($id_product);

        // Si le produit a une image et qu'elle existe on la supprime
        if ($product->$field_name != "") {
            $imagePath = dirname(__FILE__) . '/views/videos/' . $product->$field_name;
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        $savePath = str_replace('\\', '/', dirname(__FILE__) . '/views/videos/');
        $uploader = new Uploader('file');
        $file = $uploader->setSavePath($savePath)
            ->setAcceptTypes(['mp4', 'mov', 'webm'])
            ->process();

        $fileSavePath = str_replace('\\', '/', $file[0]['save_path']);
        $fileName = ltrim(str_replace($savePath, '', $fileSavePath), '/');

        try {
            //Sauvegarde la valeur de l'image pour le produit
            $product->$field_name = $fileName;
            $product->save();
        } catch (PrestaShopException $e) {
            echo $e->getMessage();
            return false;
        }

        return true;
    }


    /**
     * Suppression de l'image d'un produit
     * @param $id_product
     * @param $field_name
     * @return bool
     */
    protected function _deleteProductImageField($id_product, $field_name)
    {
        try {
            //Sauvegarde d'une valeur vide pour le produit
            $product = new Product($id_product);


            //Si le produit a une image et qu'elle existe on la supprime
            if ($product->$field_name != "") {
                $imagePath = dirname(__FILE__) . '/views/videos/' . $product->$field_name;
                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }
            $product->$field_name = '';
            $product->save();
        } catch (PrestaShopException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Hook header
     *
     * @return void
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');

        $this->context->controller->addJS($this->_path . 'views/js/front.js');
    }


    public function hookdisplayAdminProductsExtra($params)
    {
        //Lien qui va servir pour les actions du module
        $moduleLink = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
        $product = new Product($params['id_product']);
        $this->context->smarty->assign(
            array(
                'file_dir' => $this->context->link->getBaseLink() . '/modules/' . $this->name . '/views/videos/',
                'video_name' => $product->video_name,
                'moduleLink' => $moduleLink,
                'id_product' => $params['id_product'],
            )
        );

        return $this->display(__FILE__, 'views/templates/hook/extrafields.tpl');
    }

    //Custom hook to add a video to the product page
    public function hookdisplayProductAnimatedVideo($params)
    {
        $product = new Product($params['id_product']);

        $this->context->smarty->assign(
            array(
                'file_dir' => $this->context->link->getBaseLink() . '/modules/' . $this->name . '/views/videos/',
                'video_name' => $product->video_name,
            )
        );

        return $this->display(__FILE__, 'views/templates/hook/displayProductAnimatedVideo.tpl');
    }

}
