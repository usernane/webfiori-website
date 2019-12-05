<?php
namespace webfiori\views\learn\cron;
use webfiori\views\learn\LearnView;
/**
 * Description of Index
 *
 * @author Ibrahim
 */
class Index extends LearnView{
    public function __construct() {
        parent::__construct('Cron Jobs','Learn about how to setup '
                . 'cron jobs using WebFiori Framework.');
        $this->createHeaderSection(array(
            'intro'=>'Introduction',
            'class-cron-job'=>'The class CronJob',
            'class-cron'=>'The class Cron',
            'setup-cron'=>'Setting up Cron in cPanel',
            'basic-job'=>'Creating Basic Cron Job',
            'cron-log'=>'Execution Log',
            'force-job'=>'Force a Job to Execute',
            'protecting-jobs'=>'Setup A Password to Protect Your Jobs'
        ));
        $this->display();
    }

    public function createAsidNav() {
        return new \phpStructs\html\HTMLNode();
    }

}
new Index();