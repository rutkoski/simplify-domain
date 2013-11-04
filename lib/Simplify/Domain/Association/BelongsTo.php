<?php

/**
 * SimplifyPHP Framework
 *
 * This file is part of SimplifyPHP Framework.
 *
 * SimplifyPHP Framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimplifyPHP Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */

/**
 *
 * Domain Belongs To Association
 *
 */
class Simplify_Domain_Association_BelongsTo extends Simplify_Domain_Association
{

  public function beforeSave(Entity $entity, $name)
  {
    if ($entity->has($name)) {
      $source = $entity->getModel();
      $target = $this->getTargetModel();

      $localKey = $this->getLocalKey($source);

      $foreignKey = $this->getForeignKey($source);

      $row = $entity->{$name};

      Simplify_Domain_RepositoryManager::factory($target)->save($row);

      $entity[$localKey] = $row[$foreignKey];
    }
  }

  public function loadData(Entity $entity, $name)
  {
    if (! $entity->has($name)) {
      $source = $entity->getModel();
      $target = $this->getTargetModel();

      $localKey = $this->getLocalKey($source);
      $localKeyValue = $entity->get($localKey);

      $foreignTable = $target->getTable();
      $foreignKey = $target->getAttribute($this->getForeignKey($source))->getColumn();

      $params = array(
        'where' => "{$foreignTable}.{$foreignKey} = ?",
        'data' => $localKeyValue
      );

      $data = Simplify_Domain_RepositoryManager::factory($this->getTargetModel())->find(null, $params);

      $entity->set($name, $data);
      $entity->commit($name);
    }
  }

  public function getJoinExpression(EntityModel $source)
  {
    $target = $this->getTargetModel();

    $foreignTable = $target->getTable();
    $foreignKey = $target->getAttribute($this->getForeignKey($source))->getColumn();

    $localTable = $source->getTable();
    $localKey = $source->getAttribute($this->getLocalKey($source))->getColumn();

    return "{$foreignTable} ON ({$localTable}.{$localKey} = {$foreignTable}.{$foreignKey})";
  }

  protected function getLocalKey(EntityModel $source)
  {
    if (! isset($this->model['localKey'])) {
      $target = $this->getTargetModel();

      $this->model['localKey'] = Simplify_Inflector::variablize($target->getName() . '_' . $target->getPrimaryKey());
    }

    return $this->model['localKey'];
  }

  protected function getForeignKey(EntityModel $source)
  {
    if (! isset($this->model['foreignKey'])) {
      $target = $this->getTargetModel();

      $this->model['foreignKey'] = $target->getPrimaryKey();
    }

    return $this->model['foreignKey'];
  }

}
