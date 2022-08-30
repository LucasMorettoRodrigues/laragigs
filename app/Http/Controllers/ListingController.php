<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // // Show all listings - NO PAGINTION
    // public function index() {
    //     return view('listings.index', [
    //         'listings' => Listing::latest()->filter
    //         (request(['tag', 'search']))->get()
    //     ]);
    // }

    // Show all listings
    public function index() {
        return view('listings.index', [
            'listings' => Listing::latest()->filter
            (request(['tag', 'search']))->paginate(4)
        ]);

    // simplePaginate(2) -> Para paginação sem o número, apenas 'anterior ou proximo'
    }

    // Show Create Form
    public function create() {
        return view('listings.create');
    }

    // Store Listing Data
    public function store(Request $request) {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);

        return redirect('/')->with('message', 'Listing created successfully!');
    }

    // Show Edit Form
    public function edit(Listing $listing) {
        return view('listings.edit', [
            'listing' => $listing
        ]);
    }

    // Update Listing Data
    public function update(Request $request, Listing $listing) {

        // Check listing owner
        if($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action.');
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $listing->update($formFields);

        return back()->with('message', 'Listing updated successfully!');
    }

    // Delete Listing 
    public function destroy(Listing $listing) {

        // Check listing owner
        if($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action.');
        }

        $listing->delete();
    
        return redirect('/')->with('message', 'Listing deleted successfully!');
    }

    // Show single listing
    public function show(Listing $listing) {
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    // Manage Listings
    public function manage() {
        return view('listings.manage', ['listings' => User::find(auth()->id())->listings()->get()]);
    }

//     public function show($id) {
//     $listing = Listing::find($id);

//     if($listing) {
//         return view('listing', [
//             'listing' => $listing
//         ]);
//     } else {
//         abort('404');
//     }
// });
}
