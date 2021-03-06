<?php

// Include libraries and objects
require_once 'vendor/autoload.php';
require_once 'SearchConfigForm.class.php';
require_once 'config.php';

// Get areas/locations and attributes
$areas = \tabs\api\utility\Utility::getAreasAndLocations();
$info = \tabs\api\utility\Utility::getApiInformation();
$searchTerms = $info->getSearchTerms();

if (count($_POST) > 0) {

    $filters = array();
    $postArray = array_filter($_POST, function($val) {
        if (is_string($val)) {
            return (strlen($val) > 0);
        } else {
            return true;
        }
    });

    foreach (array('destination', 'area', 'location') as $key) {
        if (array_key_exists($key, $postArray)
            && is_array($postArray[$key])
        ) {
            $postArray[$key] = implode(
                '|',
                array_filter($postArray[$key])
            );
        }
    }

    // Apply attribute filters
    foreach ($postArray as $key => $val) {
        if (isset($postArray[$key . '_op'])) {
            if (isset($postArray[$key . '_between'])) {
                if (strlen($postArray[$key . '_between']) > 0) {
                    $filters[$key] = $val . '-' . $postArray[$key . '_between'];
                }
            } else {
                $filters[$key] = $postArray[$key . '_op'] . $val;
            }
        } else {
            if (!stristr($key, '_op') && !stristr($key, '_between')) {
                $filters[$key] = $val;
            }
        }
    }

    // Remove any invalid array filters (i.e. multiselect)
    foreach (array_keys($filters) as $key) {
        if (!array_key_exists($key, $searchTerms)) {
            unset($filters[$key]);
        }
    }

    $searchHelper = new \tabs\api\property\SearchHelper(
        $filters,
        array(),
        basename(__FILE__)
    );

    $searchHelper->search();
    $filter = false;
    if ($searchHelper->search()) {
        if ($searchHelper->getSearch()->getFilter() != '') {
            $filter = str_replace(
                ':',
                '&',
                $searchHelper->getSearch()->getFilter()
            );
        }
        if (isset($postArray['destination'])) {
            $filter .= '&destination=' . $postArray['destination'];
            $filter = ltrim($filter, '&');
        }
    }
    die(
        json_encode(
            array(
                'filter' => ($filter) ? urldecode($filter) : 'No filter available',
                'amount' => $searchHelper->getTotal()
            )
        )
    );
}

usort($searchTerms, function($a, $b) {
    return ($a->getLabel() > $b->getLabel());
});

$searchTerms = array_filter($searchTerms, function($ele) use ($brandcode) {
    return (in_array($ele->getBrand(), array('', 'ALL', strtoupper($brandcode))));
});

$form = SearchConfigForm::factory(
    array(
        'class' => 'form-horizontal'
    ),
    $_GET,
    $areas,
    $searchTerms,
    $brandcode
);

$form->getElementBy('getName', 'location-code')->setName('location');
$form->getElementBy('getName', 'distance-from-coordinates')->setName('distance');

foreach (array('destination', 'area', 'location') as $key) {
    if ($form->getElementBy('getName', $key)) {
        $form->getElementBy('getName', $key)
            ->setName($key . '[]')
            ->setAttribute('multiple', 'multiple')
            ->addClass('multiselect');
    }
}

// Set template to bootstrap
$form->each('getType', 'label', function($ele) {
    $ele->setClass('control-label')
        ->setTemplate(
            '<div class="control-group">
                <label{implodeAttributes}>{getLabel}</label>
                <div class="controls">
                    {renderChildren}
                </div>
            </div>'
        );
});

// Set size of the operand selects
$form->each('getType', 'select', function($ele) {
    if (stristr($ele->getName(), '_op')) {
        $ele->setAttribute('style', 'width: 100px; margin-left: 10px;');
    }
});

// Set size of the attribute text boxes
$form->each('getType', 'text', function($ele) {
    if (stristr($ele->getName(), 'ATTR')
        || in_array($ele->getName(), array('accommodates', 'rating', 'bedrooms'))
    ) {
        $ele->setAttribute('style', 'width: 50px;');
    }
    if (stristr($ele->getName(), '_between')) {
        $ele->setAttribute('style', 'width: 50px; margin-left: 10px; display: none;');
    }
});

// Set the submit button template
$form->getElementBy('getType', 'submit')
->setClass('btn btn-primary')
->setTemplate(
    '<div class="form-actions navbar-fixed-bottom">
        <input type="{getType}"{implodeAttributes}>
        <p class="pull-right" id="amount">You search will find all properties</p>
    </div>'
);

?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>TOCC Search filter config</title>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link href="datepicker/css/datepicker.css" rel="stylesheet">
    <link href="bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>TOCC Filter Config</h1>
        <p>Select the brand you wish to configure your search for.</p>
        <ul>
            <?php
                foreach ($brands as $brand) {
                    echo sprintf(
                        '<li><a href="?brandcode=%s"%s>%s</a></li>',
                        strtolower($brand['brandcode']),
                        (($brandcode == strtolower($brand['brandcode'])) ? ' style="text-decoration: underline;"' : ''),
                        $brand['name']
                    );
                }
            ?>
        </ul>

        <div style="margin-bottom: 100px;">
        <?php
            echo $form;
        ?>
        </div>
    </div>
    <div class="modal hide fade" id="modal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Click on the area below to highlight</h3>
        </div>
        <div class="modal-body">
            <textarea class="well" onClick="this.select();" style="width: 92%;">
                Model Body
            </textarea>
        </div>
        <div class="modal-footer">
            <a href="#" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
        </div>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    <script src="datepicker/js/bootstrap-datepicker.js"></script>
    <script src="bootstrap-multiselect/js/bootstrap-multiselect.js"></script>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('.multiselect').multiselect({
                maxHeight: 200,
                enableFiltering: true
            });

            jQuery('.form-horizontal select, .form-horizontal input').change(function() {
                if (jQuery(this).attr('name').indexOf('_op') > 0) {
                    var name = jQuery(this).attr('name').replace('_op', '');
                    if (jQuery(this).val() == '-') {
                        jQuery('input[name=' + name + '_between]').show();
                    } else {
                        jQuery('input[name=' + name + '_between]').val('').hide();
                    }
                }
                getAmount();
            });
            jQuery('#fromDate, #toDate').datepicker(
                {
                    format: 'dd-mm-yyyy'
                }
            ).on('changeDate', getAmount);

            jQuery('.form-horizontal').submit(function(e) {
                e.preventDefault();
                jQuery('.moda-body .well').html();
                $form = jQuery('.form-horizontal');
                jQuery.postJSON('', $form.serialize(), function(json) {
                    jQuery('.modal-body .well').html(json.filter);
                    jQuery('#modal').modal();
                });
            });
        });
        function getAmount() {
            $form = jQuery('.form-horizontal');
            jQuery('input[type=submit]', $form).addClass('disabled').val('Please Wait...');
            jQuery('#amount').html('Please wait...');
            jQuery.postJSON('', $form.serialize(), function(json) {
                jQuery('#amount').html('Your search will now find ' + json.amount + ' properties');
                jQuery('input[type=submit]', $form).removeClass('disabled').val('Create Filter');
            });
        }

        /**
         * Shortcut to post json data to a url
         * @param url A string containing the URL to which the request is sent. (put any get parameters into the url)
         * @param data A map or string that is sent to be posted to the server with the request.
         * @param callback A callback function that is executed if the request succeeds.
         */
        jQuery.postJSON = function(url, data, callback) {
            jQuery.post(url, data, callback, "json");
        }
    </script>
</body>
</html>
