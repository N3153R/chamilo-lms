<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class ExtraField
 *
 * @ORM\Entity
 * @ORM\Table(name="extra_field_options")
 *
 * @ORM\MappedSuperclass
 */
class ExtraFieldOptions
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Chamilo\CoreBundle\Entity\ExtraField")
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id")
     */
    protected $field;

    /**
     * @var string
     *
     * @ORM\Column(name="option_value", type="text", nullable=true)
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(name="display_text", type="string", length=64, nullable=true)
     */
    protected $displayText;

    /**
     * @var string
     *
     * @ORM\Column(name="priority", type="string", length=255, nullable=true)
     */
    protected $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="priority_message", type="string", length=255, nullable=true)
     */
    protected $priorityMessage;

     /**
     * @var integer
     *
     * @ORM\Column(name="option_order", type="integer", nullable=true)
     */
    protected $optionOrder;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getOptionOrder()
    {
        return $this->optionOrder;
    }

    /**
     * @param int $optionOrder
     *
     * @return $this
     */
    public function setOptionOrder($optionOrder)
    {
        $this->optionOrder = $optionOrder;

        return $this;
    }

    /**
     * @return ExtraField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param ExtraField $field
     *
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayText()
    {
        return \ExtraFieldOption::translateDisplayName($this->displayText);
    }

    /**
     * @param string $displayText
     *
     * @return $this
     */
    public function setDisplayText($displayText)
    {
        $this->displayText = $displayText;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return ExtraFieldOptions
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriorityMessage()
    {
        return $this->priorityMessage;
    }

    /**
     * @param string $priorityMessage
     *
     * @return ExtraFieldOptions
     */
    public function setPriorityMessage($priorityMessage)
    {
        $this->priorityMessage = $priorityMessage;

        return $this;
    }

}
