<?php

namespace GeoSot\EnvEditor\Facades;

use Illuminate\Support\Facades\Facade;

class EnvEditor extends Facade
{

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return \GeoSot\EnvEditor\EnvEditor::class;
	}
}