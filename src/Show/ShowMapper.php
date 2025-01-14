<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Show;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * This class is used to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ShowMapper extends BaseGroupedMapper
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $list;

    /**
     * @var ShowBuilderInterface
     */
    protected $builder;

    public function __construct(
        ShowBuilderInterface $showBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        parent::__construct($showBuilder, $admin);
        $this->list = $list;
    }

    /**
     * @param FieldDescriptionInterface|string $name
     * @param string|null                      $type
     * @param array<string, mixed>             $fieldDescriptionOptions
     *
     * @throws \LogicException
     *
     * @return static
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = [])
    {
        if (!$this->shouldApply()) {
            return $this;
        }

        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $this->addFieldToCurrentGroup($fieldKey);

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (\is_string($name)) {
            if (!$this->admin->hasShowFieldDescription($name)) {

                // NEXT_MAJOR: Remove the check and use `createFieldDescription`.
                if (method_exists($this->admin, 'createFieldDescription')) {
                    $fieldDescription = $this->admin->createFieldDescription(
                        $name,
                        $fieldDescriptionOptions
                    );
                } else {
                    $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                        $this->admin->getClass(),
                        $name,
                        $fieldDescriptionOptions
                    );
                }
            } else {
                throw new \LogicException(sprintf(
                    'Duplicate field name "%s" in show mapper. Names should be unique.',
                    $name
                ));
            }
        } else {
            throw new \TypeError(
                'Unknown field name in show mapper.'
                    .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        if (null === $fieldDescription->getLabel('sonata_deprecation_mute')) {
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription, $this->admin);
        }

        return $this;
    }

    public function get($name)
    {
        return $this->list->get($name);
    }

    public function has($key)
    {
        return $this->list->has($key);
    }

    public function remove($key)
    {
        $this->admin->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    final public function keys()
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys)
    {
        $this->admin->reorderShowGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    protected function getGroups()
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->admin->getShowGroups('sonata_deprecation_mute');
    }

    protected function setGroups(array $groups)
    {
        $this->admin->setShowGroups($groups);
    }

    protected function getTabs()
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->admin->getShowTabs('sonata_deprecation_mute');
    }

    protected function setTabs(array $tabs)
    {
        $this->admin->setShowTabs($tabs);
    }

    protected function getName()
    {
        return 'show';
    }
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
