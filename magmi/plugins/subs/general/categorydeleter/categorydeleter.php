<?php

class CategoryDeleter extends Magmi_GeneralImportPlugin
{
	public function getPluginInfo()
	{
		return array(
			"name" => "Category Deleter",
			"author" => "Andreas Bilz",
			"version" =>"0.0.1",
			"url" => ''
		);
	}

	public function afterImport()
	{
		$del_tables = array(
			$this->tablename('catalog_category_entity'),
			$this->tablename('catalog_category_entity_datetime'),
			$this->tablename('catalog_category_entity_decimal'),
			$this->tablename('catalog_category_entity_int'),
			$this->tablename('catalog_category_entity_text'),
			$this->tablename('catalog_category_entity_varchar'),
		);
		$cat_table = $this->tablename('catalog_category_entity');
		$cats_products = $this->tablename('catalog_category_product');
		$sql =<<<SQL
			SELECT entity_id FROM catalog_category_entity WHERE entity_id NOT IN(
				SELECT c.entity_id
				FROM {$cat_table} c
				JOIN {$cats_products} cp ON c.entity_id = cp.category_id
				GROUP BY cp.category_id
			) AND parent_id != 0 AND level != 0
SQL;
		$empty_ids = implode(',', array_column($this->selectAll($sql), 'entity_id'));
		if(empty($empty_ids))
			return;
		foreach ($del_tables as $del_table) {
			$sql = "DELETE FROM {$del_table} WHERE entity_id IN({$empty_ids})";
			$this->delete($sql);
		}
		return;
	}

	public function initialize($params)
	{

	}
}