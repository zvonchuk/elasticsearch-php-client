<?php

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\Conflict409Exception;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\Common\Exceptions\RoutingMissingException;

trait Elasticable
{
	
	/**
	 * @return array
	 * @throws BadRequest400Exception
	 */
	public function getOrCreate(): array
	{
		
		$tryToGet = $this->indexExists();
		
		if (!$tryToGet) {
			$index = $this->createIndex();

			elastic()->indices()->putMapping(([
				'index' => $this->index,
				'body' => [
					'properties' => $this->mapping_properties
				],
				'client' => [
					'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
				]
			]));
		} else {
            $index = elastic()->indices()->getMapping(['index' => $this->index]);

        }

		return $index;
	}
	
	public function indexExists(?string $customIndexName = NULL): bool
	{
		return elastic()->indices()->exists(['index' => $customIndexName ?? $this->index]);
	}
	
	/**
	 * @param null|string $customIndexName
	 *
	 * @return array
	 * @throws BadRequest400Exception
	 */
	public function createIndex(?string $customIndexName = NULL): array
	{
		try {
			return elastic()->indices()->create([
				'index' => $customIndexName ?? $this->index,
				'body' => $this->params,
				'client' => [
					'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
				]
			]);
		} catch (BadRequest400Exception $e) {
			if ($this->parseCauseReason($e) === "resource_already_exists_exception") {
				return [
					'acknowledged' => true,
					'index' => $customIndexName ?? $this->index,
				];
			}
			
			throw $e;
		}
	}
	
	public function parseCauseReason(Throwable $e)
	{
		return json_decode($e->getMessage())->error->root_cause[0]->type;
	}
	
	/**
	 * @param int $id
	 *
	 * @return array
	 * @throws NoNodesAvailableException
	 */
	public function delete(int $id): array
	{
		if ($this->exists($id)) {
			return elastic()->delete([
				'index' => $this->index,
				'id' => $id,
				'client' => [
					'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
				]
			]);
		}
		
		return [
			'result' => 'deleted',
			'_id' => $id,
		];
	}
	
	/**
	 * @param int $id
	 *
	 * @return bool
	 * @throws RoutingMissingException
	 * @throws Missing404Exception
	 */
	public function exists(int $id)
	{
		return elastic()->exists([
			'index' => $this->index,
			'id' => $id,
			'client' => [
				'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
			]
		]);
	}
	
	public function get(int $id): array
	{
		return elastic()->get([
			'index' => $this->index,
			'id' => $id,
			'client' => [
				'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
			]
		]);
	}
	
	public function update(int $id, array $data): array
	{
		return elastic()->update([
			'index' => $this->index,
			'id' => $id,
			'body' => [
				'doc' => $data
			],
			'client' => [
				'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
			]
		]);
	}
	
	public function deleteIndex(?string $customIndexName = NULL): array
	{
		try {
			return elastic()->indices()->delete([
				'index' => $customIndexName ?? $this->index,
			]);
			
		} catch (Missing404Exception $e) {
			return [
				'acknowledged' => true,
			];
		}
	}
	
	/**
	 * @param int $id
	 * @param array $data
	 *
	 * @return array|bool
	 * @throws Missing404Exception
	 * @throws RoutingMissingException
	 * @throws Conflict409Exception
	 */
	public function insertIfNotExists(int $id, array $data)
	{
		if (!$this->exists($id)) {
			return $this->insert($id, $data);
		}
		
		return false;
	}
	
	/**
	 * @param int $id
	 * @param array $data
	 *
	 * @return array
	 * @throws Conflict409Exception
	 */
	public function insert(int $id, array $data): array
	{
		try {
			return elastic()->create([
				'index' => $this->index,
				'id' => $id,
				'body' => $data,
				'client' => [
					'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
				]
			]);
			
		} catch (Conflict409Exception $e) {
			if ($this->parseCauseReason($e) === "version_conflict_engine_exception") {
				return [
					'result' => 'created',
					'ignored_insert' => true,
					'_index' => $customIndexName ?? $this->index,
					'_id' => $id,
				];
			}
			
			throw $e;
		} catch (Throwable $e) {
			throw $e;
		}
	}
	
	/**
	 * @throws NoNodesAvailableException
	 */
	public function refresh()
	{
		return elastic()->indices()->refresh(['index' => $this->index]);
	}
	
	/**
	 * @param array $body
	 *
	 * @param array $additionalParams
	 *
	 * @return array
	 */
	public function search(array $body, array $additionalParams = [])
	{
		$params = [
			'index' => $this->index,
			'body' => $body,
			'client' => [
				'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
			]
		];
		
		if (array_key_exists('from', $params['body']) && array_key_exists('size', $params['body']))
			unset ($params['scroll']);
		
		return elastic()->search(array_merge($params, $additionalParams));
	}
	
	/**
	 * @param array $body
	 *
	 * @return array
	 * @throws NoNodesAvailableException
	 */
	public function bulk(array $body)
	{
		return elastic()->bulk([
			'body' => $body,
			'client' => [
				'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
			]
		]);
	}
	
	public function getMapping(): array
	{
		$mapping = elastic()->indices()->getMapping([
			'index' => $this->index,
			'client' => [
				'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
			]
		]);
		
		return $mapping[$this->index]['mappings']['properties'];
	}
	
	public function count(array $body): array
	{
		
		return elastic()->count([
			'index' => $this->index,
			'body' => $body,
			'client' => [
				'curl' => [CURLOPT_HTTPHEADER => array('Content-type: application/json')]
			]
		]);
	}
}