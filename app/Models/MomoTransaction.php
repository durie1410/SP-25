class MomoTransaction {
    public $id;
    public $amount;
    public $transactionId;
    public $createdAt;
    public $updatedAt;

    public function __construct($id, $amount, $transactionId) {
        $this->id = $id;
        $this->amount = $amount;
        $this->transactionId = $transactionId;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = $this->createdAt;
    }

    public function save() {
        // Code to save transaction to database
    }

    public function update() {
        // Code to update transaction in database
        $this->updatedAt = date('Y-m-d H:i:s');
    }
}