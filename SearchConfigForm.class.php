<?php

/**
 * Search Config form object
 *
 * PHP Version 5.3
 *
 * @category  Forms
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2013 Alex Wyett
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      http://www.github.com/alexwyett
 */

/**
 * Search Config form object.  Extends the generic form and provides a static helper
 * method to build the form object
 *
 * PHP Version 5.3
 * 
 * @category  Forms
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2013 Alex Wyett
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      http://www.github.com/alexwyett
 */
class SearchConfigForm extends \aw\formfields\forms\StaticForm
{
    /**
     * Constructor
     * 
     * @param array $attributes       Form attributes
     * @param array $formValues       Form Values
     * @param array $searchAreas      Tabs Api Area objects
     * @param array $searchAttributes Tabs Api Attribute objects
     * 
     * @return void
     */
    public static function factory(
        $attributes = array(),
        $formValues = array(),
        $searchAreas = array(),
        $searchAttributes = array()
    ) {
        // New form object
        $form = new \aw\formfields\forms\Form($attributes, $formValues);
        
        // Add location fieldset if locations are present in api
        if (count($searchAreas) > 0) {        
            // Fieldset
            $fs = \aw\formfields\fields\Fieldset::factory(
                'Property Location',
                array(
                    'class' => 'property-location'
                )
            );
            
            $areas = array('Any' => '');
            $locations = array('Any' => '');
            foreach ($searchAreas as $area) {
                $areas[$area->getName()] = $area->getCode();
                if ($area->getLocations() > 0) {
                    foreach ($area->getLocations() as $location) {
                        $locations[$location->getName()] = array(
                            'value' => $location->getCode(),
                            'class' => 'area' . $area->getCode()
                        );
                    }
                }
            }
            $fs->addChild(
                self::getNewLabelAndSelect(
                    'Area',
                    $areas
                )
            );
            
            if (count($locations) > 1) {
                $fs->addChild(
                    self::getNewLabelAndSelect(
                        'Location',
                        $locations
                    )
                );
            }
        
            // Add fieldset to form
            $form->addChild($fs);
        }
        
        
        // Fieldset
        $fs = \aw\formfields\fields\Fieldset::factory(
            'Holiday Details',
            array(
                'class' => 'holiday-details'
            )
        );

        // Add fromdate field
        $fs->addChild(
            self::getNewLabelAndTextField(
                'Arrival'
            )->getElementBy('getType', 'text')
                ->setName('fromDate')
                ->setId('fromDate')
                ->getParent()
        );

        // Add todate field
        $fs->addChild(
            self::getNewLabelAndTextField(
                'Departure'
            )->getElementBy('getType', 'text')
                ->setName('toDate')
                ->setId('toDate')
                ->getParent()
        );
        
        // Add nights field
        $fs->addChild(
             self::getNewLabelAndSelect(
                'Nights', 
                array(
                    'Any' => '',
                    '2 Nights' => '2',
                    '3 Nights' => '3',
                    '4 Nights' => '4',
                    '5 Nights' => '5',
                    '6 Nights' => '6',
                    '1 Week' => '7',
                    '8 Nights' => '8',
                    '9 Nights' => '9',
                    '10 Nights' => '10',
                    '11 Nights' => '11',
                    '12 Nights' => '12',
                    '13 Nights' => '13',
                    '2 Weeks' => '14',
                    '3 Weeks' => '21',
                    '4 weeks' => '28',
                )
            )
        );
        
        // Add fieldset to form
        $form->addChild($fs);
        
        
        
        // Fieldset
        $fs = \aw\formfields\fields\Fieldset::factory(
            'Property Details',
            array(
                'class' => 'property-details'
            )
        );

        // Add reference field
        $fs->addChild(
            self::getNewLabelAndTextField(
                'Property Reference'
            )->getElementBy('getType', 'text')
                ->setName('reference')
                ->getParent()
        );
        
        // Add sleeps field
        $fs->addChild(
            self::getNewLabelAndTextField(
                'Accommodates'
            )->setLabel('Sleeps')
        );
        
        // Add bedrooms field
        $fs->addChild(
            self::getNewLabelAndTextField(
                'Bedrooms'
            )
        );
        
        // Add Rating field
        $fs->addChild(
            self::getNewLabelAndTextField(
                'Rating'
            )->setLabel('Star Rating (1 to 5)')
        );
        
        // Add default attributes
        $hardCodedAttributes = array(
            'promote' => 'Promoted Property', 
            'specialOffer' => 'On Special Offer?', 
            'pets' => 'Has Pets?'
        );
        foreach ($hardCodedAttributes as $attrib => $attrLabel) {
            $fs->addChild(
                self::getNewLabelAndCheckboxField(
                    $attrLabel
                )->getElementBy('getType', 'checkbox')
                    ->setName($attrib)
                    ->setValue('true')
                    ->getParent()
            );
        }
        
        // Add between operand
        $fs->each('getType', 'text', function($ele) {
            if ($ele->getName() != 'reference') {
                $ele->getParent()->addChild(
                    \aw\formfields\fields\SelectInput::factory(
                        $ele->getName() . '_op',
                        array(
                            '=' => '',
                            '>' => '>',
                            '<' => '<',
                            'between' => '-'
                        )
                    )
                )->addChild(
                    new \aw\formfields\fields\TextInput(
                        $ele->getName() . '_between'
                    )
                );
            }
        });
        
        // Add fieldset to form
        $form->addChild($fs);
        
        
        // Add attributes fieldset if any are set
        if (count($searchAttributes) > 0) {            
            // Fieldset
            $fs = \aw\formfields\fields\Fieldset::factory(
                'Property Attributes',
                array(
                    'class' => 'property-attributes'
                )
            );
            
            foreach ($searchAttributes as $attribute) {
                if ($attribute->getType() == 'boolean') {
                    $fs->addChild(
                        self::getNewLabelAndCheckboxField(
                            $attribute->getLabel()
                        )->getElementBy('getType', 'checkbox')
                            ->setName($attribute->getCode())
                            ->setValue('true')
                            ->getParent()
                    );
                } else if ($attribute->getType() == 'Number') {
                    $fs->addChild(
                        self::getNewLabelAndTextField(
                            $attribute->getLabel()
                        )->getElementBy('getType', 'text')
                            ->setName($attribute->getCode())
                            ->getParent()
                            ->addChild(
                                \aw\formfields\fields\SelectInput::factory(
                                    $attribute->getCode() . '_op',
                                    array(
                                        '=' => '',
                                        '>' => '>',
                                        '<' => '<'
                                    )
                                )
                            )
                    );
                }
            }
        
            // Add fieldset to form
            $form->addChild($fs);
        }
        
        // Add submit button
        $form->addChild(
            new \aw\formfields\fields\SubmitButton(
                array(
                    'value' => 'Create Filter'
                )
            )
        );
        
        return $form->mapValues();
    }
}