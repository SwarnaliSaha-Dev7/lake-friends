<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerifyOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function changePassword(Request $request)
    {
        return view('change-password');
    }

    public function sendOTP(Request $request)
    {
        try {

            $user = User::where('email', $request->email)
                        ->first();

            if(!$user){
                return response()->json([
                    'statusCode' => 500,
                    'error' => "We can't find a user with that email address.",
                ]);
            }

            // return $request;
            $otp = rand(1000, 9999);
            $expirationTime = \Carbon\Carbon::now()->addMinutes(5);

            $storeOTP = VerifyOtp::create([
                'email' => $request->email,
                'otp' => $otp,
                'otp_expire' => $expirationTime,
            ]);

            // Send email to verify OTP
            $toEmail = $request->email;

            $data = [
                "otp" => $otp
            ];

            //**open this code
            // Mail::send('email.otpVerificationMail', $data, function ($message) use ($toEmail) {
            //     $message->to($toEmail) // Use the recipient's email
            //         ->subject('Email Verification OTP');
            //     $message->from(env('MAIL_FROM_ADDRESS'), "Lake Friends Club");
            // });


            // $email = $request->email;
            // $name = $request->f_name;

            // $data = array("email" => $email, "otp" => $emailOTP, "name" => $name);

            // // // Send email
            // // Mail::send('email.sendOTP', $data, function ($message) use ($email) {
            // //     $message->to($email) // Use the recipient's email
            // //         ->subject('Verify Your Email to Complete Your FindMyGuru Signup!');
            // //     $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            // // });

            // //Fetch Email template
            // $email_template = DB::table('mail_templates')
            //                     ->where('template_name','signup_verification_otp')
            //                     ->first();

            // // Replace placeholders with dynamic values
            // $emailData = [
            //     'name' => $name,
            //     'otp' => $emailOTP,
            // ];

            // $mailBody = $email_template->body;
            // foreach ($emailData as $key => $value) {
            //     $mailBody = str_replace("{{" . $key . "}}", $value, $mailBody);
            // }

            // $data['body'] = $mailBody;
            // $data['style'] = $email_template->style;

            // // Send email
            // Mail::send('email.commonMailTemplate', $data, function ($message) use ($email,$email_template) {
            //     $message->to($email) // Use the recipient's email
            //         ->subject($email_template->subject);
            //     $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            // });

            // if ($request->phone) {
            //     $templateId = "674f05a7d6fc054fe8452692";
            //     // $mobile = "91". $request->phone;
            //     $mobile = $request->calling_code. $request->phone;
            //     $variables = [
            //         'number' => $phoneOTP,
            //         //'VAR2' => 'VALUE 2',
            //     ];
            //     $response = $this->sendSms($templateId, $mobile, $variables);
            // }

            // return $this->sendSuccessResponse('OTP sent successfully.', '');
            return response()->json(['statusCode' => 200]);
        } catch (\Throwable $th) {
            // return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
            return response()->json([
                'statusCode' => 500,
                'error' => $th->getMessage(),
                // 'error' => "Failed to send OTP, Please try again.",
            ]);
        }
    }

    public function verifyOTP(Request $request)
    {
        // return $request;
        $statusCode = "";
        $message = "";

        $verify_otp = VerifyOtp::where('email', $request->email)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$verify_otp) {
            return response()->json(['statusCode' => 500, 'message' => "OTP details not found"]);
        }

        if (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($verify_otp->otp_expire))) {
            return response()->json(['statusCode' => 500, 'message' => "OTP has expired"]);
        }

        if ($request->otp && $request->otp != $verify_otp->otp) {
            return response()->json(['statusCode' => 500, 'message' => "OTP did not match"]);
        }

        //delete the verified otp
        // $verify_otp->delete();
        $verify_otp->update([
            'is_verified' => 1
        ]);

        //Log In Or Register
        $verifyBy = $request->verifyBy;
        $contactInfo = $request->contactInfo;
        return response()->json(['statusCode' => 200, 'message' => $message]);
    }

    public function resetNewPassword(Request $request)
    {
        // user check
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'statusCode' => 404,
                'message' => 'User not found'
            ]);
        }

        // check OTP verified
        $verifyOtp = VerifyOtp::where('email', $request->email)
                                ->where('is_verified', 1)
                                ->latest()
                                ->first();

        if (!$verifyOtp) {
            return response()->json([
                'statusCode' => 403,
                'message' => 'OTP verification required'
            ]);
        }

        // // password match
        // if ($request->newPassword !== $request->confirmPassword) {
        //     return response()->json([
        //         'statusCode' => 422,
        //         'message' => 'Passwords do not match'
        //     ]);
        // }

        // update password
        $user->update([
            'password' => bcrypt($request->newPassword)
        ]);

        // delete otp after success
        $verifyOtp->delete();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Password reset successfully'
        ]);
    }


}
