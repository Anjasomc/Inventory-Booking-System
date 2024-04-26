<div>
    <x-table.controls name="User" perPage="{{ $perPage }}" />

    <div class="row">
        <div wire:poll.10s class="col-lg-12">
            <x-table>
                <x-slot name="head">
                    <x-table.row>
                        <x-table.heading direction="null">
                            <x-input.checkbox wire:model="selectPage" />
                        </x-table.heading>
                        <x-table.heading sortable wire:click="sortBy('user_full_name')" :direction="$sorts['user_full_name'] ?? null" class="col-3">User</x-table.heading>
                        <x-table.heading sortable wire:click="sortBy('email')" :direction="$sorts['email'] ?? null" class="col-3">Email</x-table.heading>
                        <x-table.heading sortable wire:click="sortBy('pos_access')" :direction="$sorts['pos_access'] ?? null" class="col-1" title="Show user on Point Of Sale Screen">Show on POS</x-table.heading>
                        <x-table.heading sortable wire:click="sortBy('booking_authoriser_user_id')" :direction="$sorts['booking_authoriser_user_id'] ?? null" class="col-3" title="Point Of Sale Booking Authoriser">POS Booking Authoriser</x-table.heading>
                        <x-table.heading class="col"/>
                    </x-table.row>

                    @if($showFilters)
                        <x-table.row>
                            <x-table.heading direction="null">
                                <x-input.checkbox />
                            </x-table.heading>
                            <x-table.heading class="col-3" direction="null"><x-input.text wire:model="filters.user_id" class="form-control-sm p-0" /></x-table.heading>
                            <x-table.heading class="col-3" direction="null"><x-input.text wire:model="filters.email" class="form-control-sm p-0" /></x-table.heading>
                            <x-table.heading class="col-1" direction="null"><x-input.text wire:model="filters.pos_access" class="form-control-sm p-0" /></x-table.heading>
                            <x-table.heading class="col-3" direction="null"><x-input.text wire:model="filters.booking_authoriser_user_id" class="form-control-sm p-0" /></x-table.heading>
                            <x-table.heading class="col" direction="null"/>
                        </x-table.row>
                    @endif
                </x-slot>

                <x-slot name="body">
                    @if($selectPage)
                        <x-table.row>
                            <x-table.cell width="12">
                                <div class="d-flex justify-content-center">
                                    @unless($selectAll)
                                        <div>
                                            <span>You selected <strong> {{ $users->count() }} </strong> users, do you want to select all <strong> {{ $users->total() }} </strong>?</span>
                                            <x-button.link wire:click="selectAll">Select All</x-button.link>
                                        </div>
                                    @else
                                        <span>You have selected all <strong> {{ $users->total() }} </strong> users.</span>
                                    @endif
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    @endif

                    @forelse ($users as $user)
                        <x-table.row wire:key="row-{{ $user->id }}">
                            <x-table.cell >
                                <x-input.checkbox wire:model="selected" value="{{ $user->id }}"></x-input.checkbox>
                            </x-table.cell>
                            <x-table.cell class="col-3"><x-link route="users" id="{{ $user->id }}" value="{{ $user->forename }} {{ $user->surname }}"></x-link></x-table.cell>
                            <x-table.cell class="col-3">{{ $user->email }}</x-table.cell>
                            <x-table.cell class="col-1">{{ $user->pos_access ? 'Yes' : 'No' }}</x-table.cell>
                            <x-table.cell class="col-3"><x-link route="users" id="{{ $user->bookingAuthoriser->id ?? '' }}" value="{{ $user->bookingAuthoriser->forename ?? '' }} {{ $user->bookingAuthoriser->surname ?? '' }}"></x-link></x-table.cell>
                            <x-table.cell class="col">
                                <x-button.primary wire:click="edit({{ $user->id }})" ><x-loading wire:target="edit({{ $user->id }})" />Edit</x-button.primary>
                                @if($user->has_account)
                                    <x-button.danger wire:click="resetPassword({{ $user->id }})" ><x-loading wire:target="resetPassword({{ $user->id }})" />Reset Password</x-button.danger>
                                @endif
                            </x-table.cell>
                        </x-table.row>
                    @empty
                        <x-table.row>
                            <x-table.cell width="12">
                                <div class="d-flex justify-content-center">
                                    No users found
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    @endforelse
                </x-slot>
            </x-table>

            <x-table.pagination-summary :model="$users" />
        </div>
    </div>

    <!-- Delete Modal -->
    <form wire:submit.prevent="deleteSelected">
        <x-modal.dialog type="confirmModal">
            <x-slot name="title">Delete Users</x-slot>

            <x-slot name="content">
                Are you sure you want to delete these users? This action is irreversible.
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$emit('hideModal','confirm')">Cancel</x-button.secondary>
                <x-button.danger type="submit">Delete</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

    <!-- Create/Edit Modal -->
    <form wire:submit.prevent="save">
        <x-modal.dialog type="editModal">
            <x-slot name="title">{{ $modalType }} User</x-slot>

            <x-slot name="content">
                <x-input.group for="forename" label="Forename" :error="$errors->first('editing.forename')">
                    <x-input.text wire:model.defer="editing.forename" id="forename" />
                </x-input.group>

                <x-input.group for="surname" label="Surname" :error="$errors->first('editing.surname')">
                    <x-input.text wire:model.defer="editing.surname" id="surname" />
                </x-input.group>

                <x-input.group for="email" label="Email" :error="$errors->first('editing.email')">
                    <x-input.text wire:model.defer="editing.email" id="email" />
                </x-input.group>

                <x-input.group for="has_account" label="Enable Dashboard Access" :error="$errors->first('editing.has_account')">
                    <x-input.checkbox wire:model.defer="editing.has_account" id="has_account" />
                </x-input.group>

                <x-input.group for="pos_access" label="Show on POS Screen" :error="$errors->first('editing.pos_access')">
                    <x-input.checkbox wire:model.defer="editing.pos_access" id="pos_access" />
                </x-input.group>
                
                <x-input.group label="POS Booking Authoriser" for="booking_authoriser_user_id" :error="$errors->first('editing.booking_authoriser_user_id')">
                    <x-input.select wire:model="editing.booking_authoriser_user_id" id="booking_authoriser_user_id" iteration="{{ $key }}">
                        @foreach ($allUsers as $user)
                            <option
                                value="{{ $user['id'] }}"
                                @if (isset($editing->bookingAuthoriser->id) && $user['id'] === $editing->bookingAuthoriser->id) selected @endif
                            >
                                {{ $user['forename'] }} {{ $user['surname'] }}
                            </option>
                        @endforeach
                    </x-input.select>
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$emit('hideModal','edit')">Cancel</x-button.secondary>
                <x-button.primary type="submit">Save</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>
</div>