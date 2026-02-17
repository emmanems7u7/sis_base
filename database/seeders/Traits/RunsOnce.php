<?php

namespace Database\Seeders\Traits;

use Illuminate\Support\Facades\DB;

trait RunsOnce
{
    public function run(): void
    {
        $class = static::class;

        $alreadyExecuted = DB::table('seeders')
            ->where('class', $class)
            ->exists();

        if ($alreadyExecuted) {
            return;
        }

        $this->handle();

        DB::table('seeders')->insert([
            'class' => $class,
            'executed_at' => now(),
        ]);
    }

    abstract protected function handle();
}
