{{-- Delete Inventory Transaction Confirmation Modal --}}
<div class="modal fade" id="deleteInventoryTransactionModal" tabindex="-1" aria-labelledby="deleteInventoryTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteInventoryTransactionModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Delete Inventory Transaction
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>This is an inventory transaction.</strong> You have two deletion options:
                </div>

                <form id="deleteInventoryTransactionForm" method="POST" action="">
                    @csrf
                    {{-- Hidden field to store restore_stock option --}}
                    <input type="hidden" name="restore_stock" id="restoreStockInput" value="1">

                    <div class="deletion-options">
                        <div class="option-card mb-3 border rounded p-3" style="cursor: pointer;" data-restore-stock="1">
                            <div class="d-flex align-items-start">
                                <div class="form-check mt-1">
                                    <input class="form-check-input deletion-option-radio" type="radio" name="deletion_option" id="optionRestore" value="1" checked>
                                </div>
                                <label class="flex-grow-1 ms-2" for="optionRestore" style="cursor: pointer;">
                                    <h6 class="mb-2 text-success">
                                        <i class="fas fa-undo-alt me-2"></i>Delete and Restore Inventory Stock
                                    </h6>
                                    <p class="mb-0 small text-muted">
                                        • Restores inventory quantity<br>
                                        • Restores stock value using unit cost<br>
                                        • Deletes related stock movement<br>
                                        • Deletes daily transaction<br>
                                        • Recalculates running balance<br>
                                        • Updates profit calculations
                                    </p>
                                    <div class="badge bg-success mt-2">Recommended</div>
                                </label>
                            </div>
                        </div>

                        <div class="option-card mb-3 border rounded p-3" style="cursor: pointer;" data-restore-stock="0">
                            <div class="d-flex align-items-start">
                                <div class="form-check mt-1">
                                    <input class="form-check-input deletion-option-radio" type="radio" name="deletion_option" id="optionPermanent" value="0">
                                </div>
                                <label class="flex-grow-1 ms-2" for="optionPermanent" style="cursor: pointer;">
                                    <h6 class="mb-2 text-danger">
                                        <i class="fas fa-trash-alt me-2"></i>Permanently Delete (Do not restore stock)
                                    </h6>
                                    <p class="mb-0 small text-muted">
                                        • Only deletes the transaction<br>
                                        • Does NOT restore inventory stock<br>
                                        • Recalculates balance normally<br>
                                        • Use when stock was actually consumed/sold
                                    </p>
                                    <div class="badge bg-danger mt-2">Use with caution</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tip:</strong> Choose "Delete and Restore Stock" if this was an error entry. 
                        Choose "Permanently Delete" only if the stock was actually consumed or sold and you want to delete the financial record only.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-2"></i>Delete Transaction
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .option-card {
        transition: all 0.2s ease;
    }

    .option-card:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .option-card.selected {
        background-color: #e7f3ff;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .deletion-option-radio {
        cursor: pointer;
    }

    .deletion-option-radio:checked ~ label {
        font-weight: 500;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('deleteInventoryTransactionModal');
    const form = document.getElementById('deleteInventoryTransactionForm');
    const restoreStockInput = document.getElementById('restoreStockInput');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const optionCards = document.querySelectorAll('.option-card');
    const radios = document.querySelectorAll('.deletion-option-radio');

    // Handle option card clicks
    optionCards.forEach(card => {
        card.addEventListener('click', function() {
            const restoreStock = this.getAttribute('data-restore-stock');
            const radio = this.querySelector('.deletion-option-radio');
            
            // Uncheck all radios and remove selected class
            radios.forEach(r => r.checked = false);
            optionCards.forEach(c => c.classList.remove('selected'));
            
            // Check this radio and add selected class
            radio.checked = true;
            this.classList.add('selected');
            
            // Update hidden input
            restoreStockInput.value = restoreStock;
        });
    });

    // Handle radio button changes
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const restoreStock = this.value;
            
            // Remove selected class from all cards
            optionCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to parent card
            this.closest('.option-card').classList.add('selected');
            
            // Update hidden input
            restoreStockInput.value = restoreStock;
        });
    });

    // Handle confirm button click
    confirmBtn.addEventListener('click', function() {
        if (form.action) {
            form.submit();
        }
    });

    // Initialize selected state on modal show
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            // Mark the first option (restore) as selected by default
            optionCards[0].classList.add('selected');
        });

        // Reset form on modal hide
        modal.addEventListener('hidden.bs.modal', function() {
            // Reset to default (restore stock)
            radios[0].checked = true;
            restoreStockInput.value = '1';
            
            // Remove selected class from all
            optionCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected to first option
            optionCards[0].classList.add('selected');
        });
    }
});
</script>
