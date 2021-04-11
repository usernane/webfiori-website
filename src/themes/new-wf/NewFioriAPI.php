<?php
namespace webfiori\theme;

use webfiori\framework\Theme;
use webfiori\framework\ui\WebPage;
use webfiori\ui\HTMLNode;
use webfiori\ui\HeadNode;
use webfiori\ui\Anchor;
use webfiori\apiParser\APITheme;
use webfiori\apiParser\FunctionDef;
use webfiori\apiParser\AttributeDef;
use webfiori\ui\UnorderedList;
use webfiori\ui\Paragraph;
use webfiori\apiParser\NameSpaceAPI;
use webfiori\ui\JsCode;


/**
 * The new WebFiori framework website theme.
 *
 * @author Ibrahim
 */
class NewFioriAPI extends APITheme {
    public function __construct() {
        parent::__construct('New Fiori API');
        $this->setVersion('1.0');
        $this->setLicenseName('MIT');
        $this->setDescription('The new WebFiori framework website theme.');
        
        $this->setAfterLoaded(function (Theme $theme) {
            $page = $theme->getPage();
            $appDiv = new HTMLNode('div', [
                'id' => 'app'
            ]);
            $vApp = new HTMLNode('v-app');
            $appDiv->addChild($vApp);
            $appDiv->addChild($appDiv);
            $body = $page->getChildByID('page-body');
            $body->setNodeName('v-main');
            
            $header = $page->getChildByID('page-header');
            $footer = $page->getChildByID('page-footer');
            $vApp->addChild($header);
            $vApp->addChild($body);
            $sideMenu = $body->getChildByID('side-content-area');
            $body->removeChild($sideMenu);
            $vApp->addChild($sideMenu);
            $vApp->addChild($footer);
            $page->removeChild($header);
            $page->removeChild($body);
            $page->removeChild($footer);
            $page->getDocument()->addChild($appDiv);
            $page->getChildByID('main-content-area')->setClassName('container');
            $page->addBeforeRender(function (WebPage $page) {
                $page->getDocument()->getBody()->addChild('script', [
                    'type' => 'text/javascript',
                    'src' => 'assets/new-wf/api-page.js',
                    'id' => 'default-vue-init'
                ]);
                $page->getDocument()->getBody()->addChild('script', [
                    'src' => 'assets/js/prism.js',
                    'type' => 'text/javascript'
                ], false);
            });
        });
    }
    public function getAsideNode() {
        $page = $this->getPage();
        $right = $page->getWritingDir() == 'rtl' ? 'right' : '';
        $sideDrawer = new HTMLNode('v-navigation-drawer', [
            'v-model' => "drawer",
            'app', $right,
            'width' => '250px',
            'app', 'temporary',
        ]);
        $sideDrawer->addChild('v-divider');
        $itemsPanel = new HTMLNode('template');
        $sideDrawer->addChild($itemsPanel);
        $itemsPanel->addChild('v-expansion-panels', [], false)
        ->addChild(
                $this->createDrawerMenuItem(
                $this->createButton([
                    'text', 'block', 
                    'href' => $this->getBaseURL().'/docs/webfiori'
                    ], 'API Reference', 'mdi-information-variant')));
        return $sideDrawer;
    }

    public function getFooterNode() {
        $page = $this->getPage();
        $footer = new HTMLNode('v-footer', [
            'padless',
        ]);
        $card = new HTMLNode('v-card', [
            'flat', 'tile', 'class' => 'flex text-center', 'dark']);
        $footer->addChild($card);
        $card->addChild('v-card-text', [], false)
                ->addChild($this->createButton([
                    'text', 
                    'fab', 
                    'x-small',
                    'target' => '_blank',
                    'href' => 'https://www.linkedin.com/in/ibrahim-binalshikh/'], null, 'mdi-linkedin'))
                ->addChild($this->createButton([
                    'text', 
                    'fab', 
                    'x-small',
                    'target' => '_blank',
                    'href' => 'https://t.me/WarriorVx'], null, 'mdi-telegram'))
                ->addChild($this->createButton([
                    'text', 
                    'fab', 
                    'x-small',
                    'target' => '_blank',
                    'href' => 'https://github.com/usernane'], null, 'mdi-github'));
        
        //
        $card->addChild('v-card-text', [], false)
        ->addChild('small', [], false)
        ->text($page->get('footer/built-with'))
         ->addChild(new Anchor('https://webfiori.com', $page->get('general/framework-name')));
        
        $card->addChild('v-divider')
        ->addChild('v-card-text', ['flat'], false)
        ->addChild('small', [], false)->text($page->get('footer/all-rights').' '.date('Y'));
        return $footer;
    }

    public function getHeadNode() {
        $head = new HeadNode();
        $head->addCSS('assets/css/prism.css');
        $head->addCSS('assets/css/code-theme.css');
        
        $head->addJs('https://unpkg.com/vue@2.x.x');
        $head->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
        $head->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css');
        $head->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css');
        $head->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js');
        $head->addJs('https://cdn.jsdelivr.net/gh/usernane/AJAXRequestJs@1.x.x/AJAXRequest.js',[
            'revision' => true
        ]);
        
        $head->addJs("https://www.googletagmanager.com/gtag/js?id=UA-91825602-2", ['async'=>''], false);
        $jsCode = new JsCode();
        $jsCode->addCode("window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-91825602-2');");
        $head->addChild($jsCode);
        return $head;
    }

    public function getHeadrNode() {
        $page = $this->getPage();
        
        $vAppBar = new HTMLNode('v-app-bar', [
            'app',
            'color' => '#d2ed9a',
            //'src' => $this->getBaseURL().'/assets/images/WFLogo512.png',
            //'hide-on-scroll',
            //'elevate-on-scroll',
            'fixed',
            'height' => '80px',
            'flat'
        ]);
        
        $vAppBar->addChild('v-app-bar-nav-icon', [
                    'class' => 'd-sm-flex d-md-none',
                    '@click' => "drawer = !drawer",
                ])->addChild('v-toolbar-title', [
                    'style' => [
                        'min-width' => '250px'
                    ],
                    'class' => 'd-none d-md-flex'
                ], false)
                ->addChild('v-row',[
                    
                ],false)
                ->addChild('v-col', [
                    'cols' => 12,
                    'md' => 4
                ], false)->addChild('img', [
                    'src' => 'assets/images/WFLogo512.png',
                    'style' => [
                        'width' => '80px'
                    ]
                ])->getParent()
                ->addChild('v-col', [
                    'cols' => 12,
                    'md' => 8,
                    'class' => 'align-center d-flex'
                ], false)
                ->addChild(new Anchor($this->getBaseURL(), 
                        $page->getWebsiteName()
                        ), [
                    'style' => [
                        'color' => 'black',
                        'text-decoration' => 'none',
                        'font-weight' => 'bold'
                    ],
                    'class' => 'site-name align-center'
                ]);
        $vAppBar->addChild('v-spacer');
        $navLinksContainer = new HTMLNode('v-container', [
            'class' => 'd-none d-md-flex'
        ]);
        $vAppBar->addChild($navLinksContainer);
        $navLinksContainer->addChild(
                self::createButton(['text', 
                    'href' => $this->getBaseURL().'/docs/webfiori'], 'API Reference'))
                ->addChild(
                self::createButton(['text', 'href' => $this->getBaseURL().'/learn'], 'Learn'))
                ->getParent()->addChild('v-spacer');
        $searchContainer = new HTMLNode('v-container', [
            'class' => 'd-flex align-center'
        ]);
        $vAppBar->addChild($searchContainer);
        $searchContainer->addChild('v-text-field', [
            'outlined', 'prepend-inner-icon' => 'mdi-magnify',
            'dense', 'rounded', 'hide-details',
        ]);
        return $vAppBar;
    }
    public static function createButton($props = [], $text = null, $icon = null, $iconProps = []) {
        $btn = new HTMLNode('v-btn', $props);
        
        if ($text !== null) {
            $btn->text($text);
        }
        if ($icon !== null) {
            $btn->addChild('v-icon', $iconProps, false)->text($icon);
        }
        return $btn;
    }
    private function createDrawerMenuItem($listTitle) {
        $item = new HTMLNode('v-list-item');
        $last = $item->addChild('v-list-item-content', [], false)
             ->addChild('v-list-item-title', [], false);
        if ($listTitle instanceof HTMLNode) {
            $last->addChild($listTitle);
        } else {
            $last->text($listTitle);
        }
        return $item;
    }

    public function createAttributeDetailsBlock(AttributeDef $attr): HTMLNode {
        $block = new HTMLNode('v-card', [
            'id' => $attr->getName(),
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ], false)->addChild($attr->getDetailsNode());
        $vCardTxt = $block->addChild('v-card-text', [], false);
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ], false)->addChild($attr->getDescriptionAsHTMLNode());
        return $block;
    }

    public function createAttributeSummaryBlock(AttributeDef $attr): HTMLNode {
        $block = new HTMLNode('v-card', [
            'height' => '85',
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ], false)->addChild($attr->getSummaryNode());
        $vCardTxt = $block->addChild('v-card-text', [], false);
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ], false)->addChild($attr->getSummaryAsHTMLNode());
        return $block;
    }

    public function createClassDescriptionNode($accessMod = '', $className = '', $ns= '', $description= ''): HTMLNode {
        $block = new HTMLNode('v-row');
        $block->addChild('v-col', [
            'cols' => 12
        ], false)->addChild('p', [], false)
        ->addChild('b', [
            'class' => 'mono'
        ], false)->text('namespace ')->addChild('a', [
            'href' => $this->getBaseURL()."/". str_replace('\\', '/', $ns)
        ], false)->text($ns);
        $block->addChild('v-col', [
            'cols' => 12
        ], false)->addChild('h1', [], false)->text($accessMod.' '.$className);
        $block->addChild('v-col', [
            'cols' => 12,
            'v-html' => "'". str_replace("'", "\'", $description)."'"
        ]);
        return $block;
    }

    public function createMethodDetailsBlock(FunctionDef $func): HTMLNode {
        $block = new HTMLNode('v-card', [
            'id' => $func->getName(),
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ], false)->addChild($func->getDetailsSignatorNode($this->getPage()));
        $vCardTxt = $block->addChild('v-card-text', [], false);
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ], false)->addChild($func->getDescriptionAsHTMLNode());
        
        if (count($func->getParameters()) != 0) {
            $paramsCol = $row->addChild('v-col', [
                'cols' => 12
            ], false);
            $paramsCol->addChild('p', [
                'style' => [
                    'font-weight' => 'bold'
                ]
            ], false)->text('Parameters:');
            $ul = $paramsCol->addChild(new UnorderedList(), [], false);
            $count = count($func->getParameters());
            for($x = 0 ; $x < $count ; $x++){
                $param = $func->getParameters()[$x];
                $optionalTxt = '';
                if($param->isOptional() === true){
                    $optionalTxt = '[Optional] ';
                }
                $li = new \webfiori\ui\ListItem();
                $ul->addChild($li, [
                    'style' => [
                        'font-family' => 'monospace'
                    ]
                ]);
                $param instanceof \webfiori\apiParser\MethodParameter;
                $li->addChild($param->getParametersNode($this->getPage()))
                        ->text(' '.$param->getName())
                        ->text($optionalTxt)
                        ->addChild($param->getDescriptionAsHTMLNode(), [
                            'style' => [
                                'font-family' => 'roboto'
                            ],
                            
                        ]);
            }
        }
        $return = $func->getMethodReturnTypesStr();
        if($return !== null){
            $retCol = $row->addChild('v-col', [
                'cols' => 12
            ], false);
            $retCol->addChild('p', [
                'style' => [
                    'font-weight' => 'bold'
                ]
            ], false)
            ->text('Returns: ', false)
            ->addChild('span', [
                'class' => 'mono'
            ], false)->text($return);
            $retCol->addChild('p', [], false)->text($func->getMethodReturnDescription());
        }
        return $block;
    }

    public function createMethodSummaryBlock(FunctionDef $func): HTMLNode {
        $block = new HTMLNode('v-card', [
            'height' => '85',
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ], false)->addChild($func->getSummarySignatorNode($this->getPage()));
        $vCardTxt = $block->addChild('v-card-text', [], false);
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ], false)->addChild($func->getSummaryAsHTMLNode());
        return $block;
    }

    public function createNSAside($links) {
        $drawer = new HTMLNode('v-navigation-drawer', [
            //'v-model' => "drawer_md",
            'fixed', 'app', 'width' => '300px',
            ':mini-variant.sync'=>"mini"
        ]);
        $list = $drawer->addChild('v-list', [], false);
        
        $list->addChild('v-list-item', [], false)
        ->addChild('v-list-item-icon', [], false)
                 ->addChild('v-icon', [], false)
                 ->text('mdi-send-circle')
                 ->getParent()->getParent()
        ->addChild('v-list-item-title', [], false)->text('All Classes')
        ->getParent()->addChild('v-btn', [
            'icon', '@click.stop' => 'mini = !mini'
        ], false)->addChild('v-icon', [], false)->text('mdi-chevron-left');
        $drawer->addChild('v-divider');
        
        
        $classes = $this->getPage()->getClasses();
        foreach ($classes as $ns => $classesInNs) {
            $subList = $list->addChild('v-list-group', [
                'dense',
                ':value' => 'true',
                'sub-group',
                'no-action'
            ], false);
            $subList->addChild('template', [
                'v-slot:activator',
                
                ], false)
            ->addChild('v-list-item-content', [], false)
            ->addChild('v-list-item-title', [], false)
            ->addChild('a', [
                     'href' => $this->getBaseURL(). str_replace('\\', '/', $ns)
                 ], false)
                 ->text($ns);
            
            foreach ($classesInNs as $className) {
                $subList->addChild('v-list-item', [
                    'dense',
                    'href' => $this->getBaseURL().$this->getPage()->getLink($className)->getAttribute('href')
                ], false)
                ->addChild('v-list-item-title', [], false)
                ->text($className);
            }
        }
        return $drawer;
    }

    public function createNamespaceContentBlock(NameSpaceAPI $nsObj): HTMLNode {
        $block = new HTMLNode('v-row');
        $block->addChild('v-col', [
            'cols' => 12
        ], false)->addChild('h1', [], false)->text('Namespace '.$nsObj->getName());
        $nsArr = $nsObj->getSubNamespaces();
        if(count($nsArr) !=0 ){
            $nsNode = $block->addChild('v-card', [
                'hover', 'outlined', 
            ], false);
            $nsNode->addChild('v-card-title', [], false)->text('All Sub-namespaces:');
            $list = $nsNode->addChild('v-card-text', [], false)
                    ->addChild('v-list', ['dense'], false);
            foreach ($nsArr as $nsName){
                $list->addChild('v-list-item', [
                    'href' => $this->getBaseURL().'/'.str_replace('\\', '/', $nsName)
                ], false)
                ->addChild('v-list-item-content', [], false)
                ->addChild('v-list-item-title',[], false)->text($nsName);
            }
        }
        $interfaces = $nsObj->getInterfaces();
        if(count($interfaces) != 0){
            $nsNode = $block->addChild('v-card', [
                'hover', 'outlined', 
            ], false);
            $nsNode->addChild('v-card-title', [], false)->text('All Interfaces:');
            $list = $nsNode->addChild('v-card-text',[], false)->addChild('v-list', ['dense'], false);
            foreach ($interfaces as $interfaceName => $infoArr){
                $link = $this->getBaseURL().'/'.str_replace('\\', '/', trim($nsObj->getName(),'\\')).'/'.$interfaceName;
                
                $list->addChild('v-list-item', [
                    'href' => $link
                ], false)
                ->addChild('v-list-item-content', [], false)
                ->addChild('v-list-item-title',[], false)->text($interfaceName)
                ->getParent()->addChild('v-list-item-subtitle', [], false)
                ->text($infoArr['summary']);
            }
        }
        $classes = $nsObj->getClasses();
        if(count($classes) != 0){
            $nsNode = $block->addChild('v-card', [
                'hover', 'outlined', 
            ], false);
            $nsNode->addChild('v-card-title', [], false)->text('All Classes:');
            $list = $nsNode->addChild('v-card-text',[], false)->addChild('v-list', ['dense'], false);
            foreach ($classes as $className => $infoArr){
                $link = $this->getBaseURL().'/'.str_replace('\\', '/', trim($nsObj->getName(),'\\')).'/'.$className;
                
                $list->addChild('v-list-item', [
                    'href' => $link
                ], false)
                ->addChild('v-list-item-content', [], false)
                ->addChild('v-list-item-title',[], false)->text($className)
                ->getParent()->addChild('v-list-item-subtitle', [], false)
                ->text($infoArr['summary']);
            }
        }
        return $block;
    }

    public function createAttrsDetailsBlock($classAttrsArr) {
        if (count($classAttrsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ], false)->addChild('h3', [], false)->text('Class Attributes Details');
            $col = $node->addChild('v-col', [], false);
            foreach ($classAttrsArr as $attrObj) {
                $col->addChild($this->createAttributeDetailsBlock($attrObj));
            }
            return $node;
        }
    }

    public function createAttrsSummaryBlock($classAttrsArr) {
        if (count($classAttrsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ], false)->addChild('h3', [], false)->text('Class Attributes Summary');
            $col = $node->addChild('v-col', [], false);
            foreach ($classAttrsArr as $attrObj) {
                $col->addChild($this->createAttributeSummaryBlock($attrObj));
            }
            return $node;
        }
    }

    public function createMethodsDetailsBlock($classMethodsArr) {
        if (count($classMethodsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ], false)->addChild('h3', [], false)->text('Class Methods Details');
            $col = $node->addChild('v-col', [], false);
            foreach ($classMethodsArr as $methObj) {
                $col->addChild($this->createMethodDetailsBlock($methObj));
            }
            return $node;
        }
    }

    public function createMethodsSummaryBlock($classMethodsArr) {
        if (count($classMethodsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ], false)->addChild('h3', [], false)->text('Class Methods Summary');
            $col = $node->addChild('v-col', [], false);
            foreach ($classMethodsArr as $methObj) {
                $col->addChild($this->createMethodSummaryBlock($methObj));
            }
            return $node;
        }
    }

}
return __NAMESPACE__;