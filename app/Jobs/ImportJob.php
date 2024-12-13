<?php

namespace App\Jobs;

use App\Models\Row;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Common\Entity\Cell;

class ImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $filename,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::disk('local')->path("uploads/{$this->filename}");

        if (! file_exists($fullPath) || ! is_readable($fullPath)) {
            throw new \Exception("invalid file: {$fullPath}");
        }

        info("{$this->filename}: starting import");

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullPath);

        $chunk = [];
        $chunkMap = []; // map ext_id to line
        $errors = [];

        $line = 0;

        $headerSkipped = false;

        foreach ($reader->getSheetIterator() as $index => $sheet) {
            if ($index != 1) {
                continue;
            }

            foreach ($sheet->getRowIterator() as $row) {
                if (! $headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }
                
                Redis::command('set', [$this->filename, $line]);
    
                /** @var Cell[] $cells */
                $cells = $row->getCells();
    
                if (count($cells) != 3) {
                    $line++;
                    continue;
                }
                
                $id = $cells[0]->getValue();
                $name = $cells[1]->getValue();
                $date = $cells[2]->getValue();
    
                if (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
                    $errors[$line][] = 1; // invalid id
                }
    
                if (preg_match('/^[a-zA-Z]+ [a-zA-Z]+$/', $name) === false) {
                    $errors[$line][] = 2; // invalid name
                }
    
                try {
                    $date = Carbon::createFromFormat("d.m.Y", $date)->format('Y-m-d');
                } catch (InvalidFormatException $e) {
                    $errors[$line][] = 3; // invalid date
                }

                
                if (! empty($errors[$line])) {
                    $line++;
                    continue;
                }
    
                $chunk[] = [
                    'ext_id' => $id,
                    'name' => $name,
                    'date' => $date,
                ];

                $chunkMap[$id] = $line;
    
                if (count($chunk) >= 1000) {
                    info("{$this->filename}: inserting chunk ($line)");

                    // checking for duplicates before inserting
                    $existing = Row::query()
                        ->whereIn('ext_id', array_column($chunk, 'ext_id'))
                        ->select('ext_id')
                        ->getQuery() // without hyrdation
                        ->get();

                    foreach ($existing as $row) {
                        $errors[$chunkMap[$row->ext_id]][] = 4; // duplicate
                    }

                    Row::query()->upsert($chunk, ['ext_id'], []);

                    $chunk = [];
                    $chunkMap = [];
                }

                $line++;
            }
        }

        info("{$this->filename}: generating result file");

        $result = "";

        foreach ($errors as $line => $lineErrors) {
            $str = ($line + 2) . " - ";

            if (in_array(1, $lineErrors)) {
                $str .= "невалидное значение поля id, ";
            }
            if (in_array(2, $lineErrors)) {
                $str .= "невалидное значение поля name, ";
            }
            if (in_array(3, $lineErrors)) {
                $str .= "невалидное значение поля date, ";
            }
            if (in_array(4, $lineErrors)) {
                $str .= "id дублируется, ";
            }

            $result .= substr($str, 0, -2) . PHP_EOL;
        }

        $resultPath = sprintf("results/%s.txt", pathinfo($this->filename, PATHINFO_BASENAME));

        Storage::disk('local')->put($resultPath, $result);

        info("{$this->filename}: import completed, result file is {$resultPath}");

        Storage::disk('local')->delete("uploads/{$this->filename}");
    }
}
