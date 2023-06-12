/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: tokenarray.h,v 1.10 2020-08-14 16:56:54 dick Exp $
*/

/* Interface for the token storage */

#include	"token.h"

extern void Init_Token_Array(void);
extern void Store_In_Token(Token tk);
extern void Free_Token_Array(void);
extern size_t Token_Array_Length(void);	/* also first free token position */

extern Token *Token_Array;

