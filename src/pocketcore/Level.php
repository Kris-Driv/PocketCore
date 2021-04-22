<?php
namespace pocketcore;

class Level {

    protected $chunks = [];

    public function __construct(array $chunks = []) {
        $this->chunks = $chunks;
    }

    public function setChunk(int $x, int $y, array $chunk) {
        $this->chunks[$x][$y] = $chunk;
    }

    public function deleteChunk(int $x, int $y) {
        unset($this->chunks[$x][$y]);
    }

    public function setChunks(array $chunks) {
        foreach($chunks as $chunk) {
            $this->chunks[$chunk['x']][$chunk['y']] = $chunk;
        }
    }

    public function getChunks(array $chunks) {
        return $this->chunks;
    }

    public function toResponse(): string {
        return json_encode([
            'type' => 'chunks',
            'body' => base64_encode(json_encode($this->chunks))
        ]);
    }

}