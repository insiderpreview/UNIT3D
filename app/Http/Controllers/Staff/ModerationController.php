<?php
/**
 * NOTICE OF LICENSE
 *
 * UNIT3D is open-sourced software licensed under the GNU General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers\Staff;

use App\User;
use App\Group;
use App\Torrent;
use App\Requests;
use App\Category;
use App\Peer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use \Toastr;

class ModerationController extends Controller
{
    /**
     * Torrent Moderation.
     *
     */
    public function moderation()
    {
        $current = Carbon::now();
        $pending = Torrent::pending()->get(); //returns all Pending Torrents
        $rejected = Torrent::rejected()->get();
        $modder = DB::table('torrents')->where('status', '=', '0')->count();

        return view('Staff.torrent.moderation', ['current' => $current, 'pending' => $pending, 'rejected' => $rejected, 'modder' => $modder]);
    }

    /**
     * Torrent Moderation -> approve
     *
     * @param $slug Slug of the torrent
     * @param $id Id of the torrent
     */
    public function approve($slug, $id)
    {
        Torrent::approve($id);

        return redirect()->route('moderation')->with(Toastr::success('Torrent Approved', 'Yay!', ['options']));
    }

    /**
     * Torrent Moderation -> reject
     *
     * @param $slug Slug of the torrent
     * @param $id Id of the torrent
     */
    public function reject($slug, $id)
    {
        Torrent::reject($id);

        return redirect()->route('moderation')->with(Toastr::error('Torrent Rejected', 'Whoops!', ['options']));
    }

    /**
     * Resets the filled and approved attributes on a given request
     * @method resetRequest
     *
     */
    public function resetRequest($id)
    {
        $user = Auth::user();
        // reset code here
        if ($user->group->is_modo) {
            $request = Requests::findOrFail($id);

            $request->filled_by = null;
            $request->filled_when = null;
            $request->filled_hash = null;
            $request->approved_by = null;
            $request->approved_when = null;
            $request->save();

            return redirect()->route('request', ['id' => $id])->with(Toastr::success("The request has been reset!", 'Yay!', ['options']));
        } else {
            return redirect()->route('request', ['id' => $id])->with(Toastr::error("You don't have access to this operation!", 'Whoops!', ['options']));
        }
        return redirect()->route('requests')->with(Toastr::error("Unable to find request!", 'Whoops!', ['options']));
    }
}
