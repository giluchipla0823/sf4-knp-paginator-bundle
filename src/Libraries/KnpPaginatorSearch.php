<?php

namespace App\Libraries;

use App\Helpers\ArrayHelper;
use App\Helpers\TextHelper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class KnpPaginatorSearch
{
    public const DEFAULT_PAGE_NUMBER = 1;
    public const ITEMS_PER_PAGE = 30;

    public const PAGINATED_ON = 1;
    public const PAGINATED_OFF = 0;

    public const FILTERS_FIELD_NAME = 'filters';
    public const SORT_FIELD_NAME = 'sort';
    public const DIRECTION_FIELD_NAME = 'direction';
    public const SORT_ASC = 'ASC';
    public const SORT_DESC = 'DESC';
    public const FILTER_TYPE_DEFAULT = 'default';
    public const FILTER_TYPE_EQUAL_TO = 'equal_to';
    public const FILTER_TYPE_CONTAIN = 'contain';
    public const FILTER_TYPE_BETWEEN = 'between';
    public const FILTER_TYPE_RANGE_VALUES = 'range_values';

    /**
     * @var array
     */
    private $aliasWithEntities = [];

    /**
     * @var array
     */
    private $excludeFilters = [];

    /**
     * @var array
     */
    private $excludeSorts = [];

    /**
     * @var QueryBuilder $builder;
     */
    private $builder;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * Handler for multiples filters and sorting.
     *
     * @param QueryBuilder $builder
     * @param Request $request
     */
    public function handle(QueryBuilder $builder, Request $request): void {
        $this->builder = $builder;
        $this->request = $request;

        $this->filtering();
        $this->sorting();
    }

    /**
     * Configuration to handle the order of fields.
     *
     * @return void
     */
    private function sorting(): void {
        if(!$this->request->query->has(self::SORT_FIELD_NAME)){
            return;
        }

        $sort = $this->transformSortBy();

        $this->request->query->remove(self::SORT_FIELD_NAME);

        if($sort){
            $this->request->query->add([self::SORT_FIELD_NAME => $sort]);
        }
    }

    /**
     * Configuration for multiple filters.
     *
     * @return void
     */
    private function filtering(): void {

        $filters = $this->transformFilters();

        foreach ($filters as $column => $values){
            $condition = implode(
                ' OR ',
                $this->generateConditionsForBuilder($column, $values)
            );

            if(!$condition){
                continue;
            }

            $this->builder->andWhere($condition);
        }
    }

    /**
     * Generate conditions for query by multiple filters.
     *
     * @param string $column
     * @param array $values
     * @return array
     */
    private function generateConditionsForBuilder(string $column, array $values): array {
        $conditions = [];

        foreach ($values as $index => $item){
            $property = $this->getParameterNameByColumn($column, $index);
            $value = $item['value'];
            $filterType = self::getColumnFilterType($item);

            if(self::checkValueIsEmpty($value)){
                continue;
            }

            $operator = self::getOperatorForBuilder($filterType);
            $value = self::getValueForBuilder($filterType, $value);
            $conditions[] = $this->getConditionForBuilder($filterType, $column, $operator, $property);

            $this->builder->setParameter($property, $value);
        }

        return $conditions;
    }

    /**
     * Get query condition by filter type.
     *
     * @param string $filterType
     * @param string $column
     * @param string $operator
     * @param string $property
     * @return string
     */
    private function getConditionForBuilder(
        string $filterType,
        string $column,
        string $operator,
        string $property
    ): string {
        if($filterType === self::FILTER_TYPE_CONTAIN){
            return "{$column} {$operator} (:{$property})";
        }

        return "{$column} {$operator} :{$property}";
    }

    /**
     * Get value for query parameter by filter type.
     *
     * @param string $filterType
     * @param mixed $value
     * @return array|false|string|string[]
     */
    public static function getValueForBuilder(string $filterType, $value) {
        switch ($filterType):
            case self::FILTER_TYPE_DEFAULT:
                $value = "%{$value}%";
                break;
            case self::FILTER_TYPE_CONTAIN:
                $value = !is_array($value) ? explode(',', $value) : $value;
                break;
            default:

        endswitch;

        return $value;
    }

    /**
     * Get query operator by filter type.
     *
     * @param string $filterType
     * @return string
     */
    public static function getOperatorForBuilder(string $filterType): string{
        switch ($filterType):
            case self::FILTER_TYPE_EQUAL_TO:
                $operator = '=';
                break;
            case self::FILTER_TYPE_CONTAIN:
                $operator = 'IN';
                break;
            default:
                $operator = 'LIKE';
        endswitch;

        return $operator;
    }

    /**
     * Get the type of filter that will be applied to a certain field or column.
     *
     * @param array $item
     * @return string
     */
    public static function getColumnFilterType(array $item): string {
        $type = isset($item['filter_type']) ? $item['filter_type'] : self::FILTER_TYPE_DEFAULT;

        if($type === 'LIKE'){
            $type = self::FILTER_TYPE_DEFAULT;
        }

        return $type;
    }

    public static function checkValueIsEmpty($value){
        return $value === '' || is_null($value);
    }

    /**
     * It transforms the input value of the order filter into the original value of a certain column defined
     * in the entities. If you do not find any column in the entity, then proceed to remove the order parameter
     * sent, to avoid errors in the sorting process.
     *
     * @return string|null
     */
    private function transformSortBy(): ?string {
        $sortBy = $this->request->query->get(self::SORT_FIELD_NAME);

        list($alias, $field) = explode('.', $sortBy);

        if(in_array($field, $this->getExcludeSorts())){
            return null;
        }

        if(!$entityClass = $this->getEntityClassFromAlias($alias)){
            return null;
        }

        $classMetadata = $this->getClassMetadataFromEntityClass($entityClass);

        if(!$attribute = $this->getAttributeFromMappingFields($alias, $field)){
            return null;
        }

        if(!$this->checkExistsAttributeInTheEntity($attribute, $classMetadata)){
            return null;
        }

        if(!in_array($alias, $this->builder->getAllAliases())){
            $this->addJoinsQueryBuilderWithBelongsToFromAlias($alias);
        }

        return "{$alias}.{$attribute}";
    }

    /**
     * It transforms the input filters into the original values of the columns defined in
     * their respective entities. In addition, it generates an alias for each filter,
     * taking as a reference the name of the table to which a given entity refers.
     *
     * @return array
     */
    private function transformFilters(): array {
        $filters = ArrayHelper::exceptItems(
            $this->request->get(self::FILTERS_FIELD_NAME, []),
            $this->getExcludeFilters()
        );

        $output = [];

        foreach ($filters as $alias => $fields){
            if(!$entityClass = $this->getEntityClassFromAlias($alias)){
                continue;
            }

            $classMetadata = $this->getClassMetadataFromEntityClass($entityClass);

            foreach ($fields as $field => $items){
                if(!$attribute = $this->getAttributeFromMappingFields($alias, $field)){
                    continue;
                }

                if(!$this->checkExistsAttributeInTheEntity($attribute, $classMetadata)){
                    continue;
                }

                if(!in_array($alias, $this->builder->getAllAliases())){
                    $this->addJoinsQueryBuilderWithBelongsToFromAlias($alias);
                }

                $output["{$alias}.{$attribute}"] = $items;
            }
        }

        return $output;
    }

    /**
     * Check if the attribute exists in the entity or in its associations mapping.
     *
     * @param string $attribute
     * @param ClassMetadata $classMetadata
     * @return bool
     */
    private function checkExistsAttributeInTheEntity(string $attribute, ClassMetadata $classMetadata): bool{
        return (
            in_array($attribute, $classMetadata->getFieldNames()) ||
            in_array($attribute, $classMetadata->getAssociationNames())
        );
    }

    /**
     * Gets ClassMetadata from the entity.
     *
     * @param string $entityClass
     * @return ClassMetadata
     */
    private function getClassMetadataFromEntityClass(string $entityClass): ClassMetadata{
        return $this->builder->getEntityManager()->getClassMetadata($entityClass);
    }

    /**
     * Obtains parameter name that will be used to assign as a value to a certain
     * field or column of the entity.
     *
     * @param string $column
     * @param int $index
     * @return string
     */
    private function getParameterNameByColumn(string $column, int $index): string {
        list(, $fieldName) = explode('.', $column);

        $fieldName = TextHelper::convertCamelCaseToSnakeCase($fieldName);

        return $fieldName . "_" . $index;
    }

    /**
     * Set exclude filter fields.
     *
     * @param array $items
     * @return $this
     */
    public function setExcludeFilters(array $items): self {
        $this->excludeFilters = $items;

        return $this;
    }

    /**
     * Get exclude filter fields.
     *
     * @return array
     */
    private function getExcludeFilters(): array {
        return $this->excludeFilters;
    }

    /**
     * Set excludes sort fields.
     *
     * @param array $items
     * @return $this
     */
    public function setExcludeSorts(array $items): self {
        $this->excludeSorts = $items;

        return $this;
    }

    /**
     * Get excludes sort fields.
     *
     * @return array
     */
    private function getExcludeSorts(): array {
        return $this->excludeSorts;
    }

    /**
     * Set alias with entities.
     *
     * @param array $alias
     * @return $this
     */
    public function setAliasWithEntities(array $alias): self {
        $this->aliasWithEntities = $alias;

        return $this;
    }

    /**
     * Set alias with entities.
     *
     * @return array
     */
    private function getAliasWithEntities(): array {
        return $this->aliasWithEntities;
    }

    /**
     * Get an entity class using an alias.
     *
     * @param string $alias
     * @return string|null
     */
    private function getEntityClassFromAlias(string $alias): ?string {
        if(!$alias = $this->getCurrentAlias($alias)){
            return null;
        }

        return $alias['entity_class'];
    }


    private function getCurrentAlias(string $alias): ?array {
        $aliases = $this->getAliasWithEntities();

        return isset($aliases[$alias]) ? $aliases[$alias] : null;
    }

    private function getMappingFieldsFromAlias(string $alias): ?array {
        if(!$alias = $this->getCurrentAlias($alias)){
            return null;
        }

        return $alias['mapping_fields'];
    }

    private function getAttributeFromMappingFields(string $alias, $field): ?string{
        $mappingFields = $this->getMappingFieldsFromAlias($alias);

        if(!$mappingFields){
            return null;
        }

        return array_key_exists($field, $mappingFields) ? $mappingFields[$field] : null;
    }

    private function getBelongsToFromAlias(string $alias): ?string {
        if(!$alias = $this->getCurrentAlias($alias)){
            return null;
        }

        return isset($alias['belongs_to']) ? $alias['belongs_to'] : null;
    }

    private function addJoinsQueryBuilderWithBelongsToFromAlias(
        string $alias
    ): void {
        if(!$belongsTo = $this->getBelongsToFromAlias($alias)){
            return;
        }

        $this->builder->addSelect([$alias])->join($belongsTo, $alias);
    }
}