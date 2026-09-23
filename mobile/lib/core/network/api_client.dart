import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';

class ApiResponse {
  final bool success;
  final String message;
  final dynamic data;
  final int statusCode;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    required this.statusCode,
  });
}

class ApiClient {
  final http.Client _client = http.Client();

  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(AppConstants.tokenKey);
  }

  Future<Map<String, String>> _getHeaders({bool isMultipart = false}) async {
    final headers = <String, String>{
      'Accept': 'application/json',
    };
    if (!isMultipart) {
      headers['Content-Type'] = 'application/json';
    }

    final token = await _getToken();
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  ApiResponse _processResponse(http.Response response) {
    try {
      final body = jsonDecode(response.body);
      final bool success = body['success'] ?? (response.statusCode >= 200 && response.statusCode < 300);
      final String message = body['message'] ?? (success ? 'Success' : 'An error occurred');
      final dynamic data = body['data'];

      return ApiResponse(
        success: success,
        message: message,
        data: data,
        statusCode: response.statusCode,
      );
    } catch (e) {
      return ApiResponse(
        success: response.statusCode >= 200 && response.statusCode < 300,
        message: response.statusCode >= 500
            ? 'Server error. Please try again later.'
            : 'Invalid response from server.',
        statusCode: response.statusCode,
      );
    }
  }

  Future<ApiResponse> get(String url) async {
    try {
      final headers = await _getHeaders();
      final response = await _client.get(Uri.parse(url), headers: headers);
      return _processResponse(response);
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Network connection error: $e',
        statusCode: 0,
      );
    }
  }

  Future<ApiResponse> post(String url, Map<String, dynamic> body) async {
    try {
      final headers = await _getHeaders();
      final response = await _client.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(body),
      );
      return _processResponse(response);
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Network connection error: $e',
        statusCode: 0,
      );
    }
  }

  Future<ApiResponse> postMultipart({
    required String url,
    Map<String, String>? fields,
    String? fileField,
    File? file,
    List<int>? fileBytes,
    String? fileName,
  }) async {
    try {
      final headers = await _getHeaders(isMultipart: true);
      final request = http.MultipartRequest('POST', Uri.parse(url));
      request.headers.addAll(headers);

      if (fields != null) {
        request.fields.addAll(fields);
      }

      if (fileField != null) {
        if (file != null && await file.exists()) {
          request.files.add(await http.MultipartFile.fromPath(fileField, file.path));
        } else if (fileBytes != null && fileName != null) {
          request.files.add(http.MultipartFile.fromBytes(fileField, fileBytes, filename: fileName));
        }
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      return _processResponse(response);
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Failed to upload document: $e',
        statusCode: 0,
      );
    }
  }
}
