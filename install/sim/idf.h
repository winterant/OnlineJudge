/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: idf.h,v 2.17 2020-08-14 16:56:51 dick Exp $
*/

/*	Idf module:
	Token Idf_In_List(
		const char *str,
		const struct idf list[],
		size_t list_size,
		Token default_token
	);
		looks up a keyword in a list of keywords l, represented as an
		array of struct idf, and returns its translation as a token;
		default_token is returned if the keyword is not found.
	Token Idf_Hashed(char *str);
		returns a token unequal to No_Token or End_Of_Line, derived
		from str through hashing
*/

#include	"token.h"

/* the struct for keywords etc. */
struct idf {
	char *id_tag;	/* an interesting identifier */
	Token id_tr;	/* with its one-Token translation */
};

/* public functions */
extern Token Idf_In_List(
	const char *str,
	const struct idf list[],
	size_t list_size,
	Token default_token
);
extern Token Idf_Hashed(const char *str);
extern void Lower_Case(char *str);
