<div>
    <button wire:click="create()" class="btn btn-primary">Create Message</button>

    <table class="table">
        <thead>
            <tr>
                <th>Text</th>
                <th>URL</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($messages as $message)
                <tr>
                    <td>{{ $message->body }}</td>
                    <td>{{ $message->target_url }}</td>
                    <td>
                        <button wire:click="edit({{ $message->id }})" class="btn btn-primary">Edit</button>
                        <button wire:click="deleteConfirmation({{ $message->id }})" class="btn btn-danger">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal for creating and editing messages -->
    <div wire:ignore.self class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">{{ $message ? 'Edit' : 'Create' }} Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <div class="form-group">
                        <label for="text">Text</label>
                        <input type="text" class="form-control" id="text" wire:model="message.subject" required>
                        @error('message.subject') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="text">Text</label>
                        <textarea class="form-control" id="body" wire:model="message.body" required></textarea>
                        @error('message.body') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="target_url" class="form-control" id="target_url" wire:model="message.target_url">
                        @error('message.target_url') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="store()" wire:loading.attr="disabled" wire:target="store">
                        <span wire:loading.remove>Save</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for deleting messages -->
    <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ $modalConfirmationText }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteMessage()" wire:loading.attr="disabled" wire:target="deleteMessage">
                        <span wire:loading.remove>Delete</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        $('#messageModal').on('hidden.bs.modal', function (e) {
            @this.resetInputFields();
        });